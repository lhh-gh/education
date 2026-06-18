<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Service\Education\Academic;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\CourseRepository;
use App\Repository\Education\Academic\LessonPackageRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class LessonPackageService
{
    public function __construct(
        private readonly LessonPackageRepository $repository,
        private readonly CourseRepository $courseRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $course = $this->enabledCourseOrFail((int) ($data['course_id'] ?? 0), $tenantId, $campusId);
        $code = trim((string) ($data['code'] ?? ''));
        $this->assertUniqueCode($tenantId, $campusId, $code);
        $data = $this->normalizedPayload($data, $tenantId, $campusId, (int) $course->id, $code, $operatorId, true);

        return Db::transaction(function () use ($data, $context): EducationLessonPackage {
            $package = $this->repository->create($data)->refresh();
            $this->dispatchAudit('created', $package, $context, [], $package->toArray());

            return $package;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
    {
        $package = $this->findForWrite($id, $context);
        $tenantId = (int) $package->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $package->campus_id, $context);
        $course = $this->enabledCourseOrFail((int) ($data['course_id'] ?? $package->course_id), $tenantId, $campusId);
        $code = trim((string) ($data['code'] ?? $package->code));
        $this->assertUniqueCode($tenantId, $campusId, $code, $id);
        $data = $this->normalizedPayload($data + $package->toArray(), $tenantId, $campusId, (int) $course->id, $code, $operatorId, false);

        return Db::transaction(function () use ($package, $data, $context): EducationLessonPackage {
            $before = $package->toArray();
            $package->fill($data);
            $package->save();
            $package = $package->refresh();
            $this->dispatchAudit('updated', $package, $context, $before, $package->toArray());

            return $package;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationLessonPackage
    {
        $package = $this->findForWrite($id, $context);
        $data = [
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ];

        return Db::transaction(function () use ($package, $data, $context): EducationLessonPackage {
            $before = $package->toArray();
            $package->fill($data);
            $package->save();
            $package = $package->refresh();
            $this->dispatchAudit('status_changed', $package, $context, $before, $package->toArray());

            return $package;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $package = $this->findForWrite($id, $context);
        if ($this->repository->hasEnrollmentReferences((int) $package->id, (int) $package->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson package is referenced by enrollments', ['id' => (int) $package->id]);
        }

        return Db::transaction(function () use ($package, $context, $operatorId): bool {
            $before = $package->toArray();
            $package->updated_by = $operatorId;
            $package->save();
            $deleted = (bool) $package->delete();
            $this->dispatchAudit('deleted', $package, $context, $before, ['id' => (int) $package->id, 'deleted' => $deleted]);

            return $deleted;
        });
    }

    public function optionsByCourse(int $courseId, EducationUserContext $context): array
    {
        return $this->repository->optionsByCourse($courseId, $context);
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationLessonPackage
    {
        $package = $this->repository->findById($id);
        if (! $package instanceof EducationLessonPackage) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson package not found in current context', ['id' => $id]);
        }

        $this->assertTenantVisible((int) $package->tenant_id, $context);
        $this->assertCampusWritable((int) $package->tenant_id, (int) $package->campus_id, $context);

        return $package;
    }

    private function enabledCourseOrFail(int $courseId, int $tenantId, int $campusId): EducationCourse
    {
        $course = $this->courseRepository->findEnabledForEnrollment($courseId, $tenantId, $campusId);
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'course is disabled', ['course_id' => $courseId]);
        }

        return $course;
    }

    private function normalizedPayload(array $data, int $tenantId, int $campusId, int $courseId, string $code, ?int $operatorId, bool $creating): array
    {
        $lessonUnits = $this->decimal($data['lesson_units'] ?? 0);
        $bonusUnits = $this->decimal($data['bonus_units'] ?? 0);
        $totalUnits = $this->decimal((float) $lessonUnits + (float) $bonusUnits);
        if ((float) $totalUnits <= 0.0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson package total units must be greater than zero');
        }

        $listPrice = $this->decimal($data['list_price'] ?? 0);
        $salePrice = $this->decimal($data['sale_price'] ?? 0);
        if ((float) $listPrice > 0.0 && (float) $salePrice > (float) $listPrice) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'sale_price must be less than or equal to list_price');
        }

        $payload = [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'code' => $code,
            'name' => $data['name'],
            'lesson_units' => $lessonUnits,
            'bonus_units' => $bonusUnits,
            'total_units' => $totalUnits,
            'list_price' => $listPrice,
            'sale_price' => $salePrice,
            'validity_days' => isset($data['validity_days']) && $data['validity_days'] !== '' ? (int) $data['validity_days'] : null,
            'status' => $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value)),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'remark' => $data['remark'] ?? null,
            'updated_by' => $operatorId,
        ];
        if ($creating) {
            $payload['created_by'] = $operatorId;
        }

        return $payload;
    }

    private function tenantIdForWrite(array $data, EducationUserContext $context): int
    {
        if ($context->platformAccess) {
            $tenantId = isset($data['tenant_id']) && $data['tenant_id'] !== '' ? (int) $data['tenant_id'] : 0;
        } else {
            if ($context->tenantId === null) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
            }
            if (isset($data['tenant_id']) && $data['tenant_id'] !== '' && (int) $data['tenant_id'] !== $context->tenantId) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $data['tenant_id']]);
            }
            $tenantId = $context->tenantId;
        }

        if (! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }

        return $tenantId;
    }

    private function campusIdForWrite(int $tenantId, mixed $campusId, EducationUserContext $context): int
    {
        if ($campusId === null || $campusId === '') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'campus_id is required');
        }

        $campusId = (int) $campusId;
        $this->assertCampusWritable($tenantId, $campusId, $context);

        return $campusId;
    }

    private function assertTenantVisible(int $tenantId, EducationUserContext $context): void
    {
        if (! $context->platformAccess && $context->tenantId !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => $tenantId]);
        }
    }

    private function assertCampusWritable(int $tenantId, int $campusId, EducationUserContext $context): void
    {
        $exists = EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($campusId)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current tenant', ['campus_id' => $campusId]);
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin) {
            return;
        }

        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => $campusId]);
        }
    }

    private function assertUniqueCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): void
    {
        if ($this->repository->existsCode($tenantId, $campusId, $code, $excludeId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson package code already exists', ['campus_id' => $campusId, 'code' => $code]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        if (AcademicRecordStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid academic record status');
        }

        return $status;
    }

    private function decimal(mixed $value): string
    {
        return number_format(round((float) $value, 2), 2, '.', '');
    }

    private function extractPage(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? $filters['per_page'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationLessonPackage $package, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'lesson_package',
            action: 'education.academic.lesson_package.' . $action,
            businessType: 'lesson_package',
            businessId: (int) $package->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $package->tenant_id, 'campus_id' => (int) $package->campus_id, 'course_id' => (int) $package->course_id],
            summary: \sprintf('Lesson package %s %s', $package->name, $action)
        ));
    }
}

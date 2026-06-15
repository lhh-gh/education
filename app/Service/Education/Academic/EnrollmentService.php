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

use App\Event\Education\Academic\EducationEnrollmentConfirmed;
use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\CourseRepository;
use App\Repository\Education\Academic\EnrollmentRepository;
use App\Repository\Education\Academic\LessonPackageRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\FeatureFlagService;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EnrollmentService
{
    private const FINANCE_GATE = 'finance_payment_enabled';

    public function __construct(
        private readonly EnrollmentRepository $repository,
        private readonly CourseRepository $courseRepository,
        private readonly LessonPackageRepository $packageRepository,
        private readonly StudentCourseAccountService $accountService,
        private readonly FeatureFlagService $featureFlagService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function detail(int $id, EducationUserContext $context): EducationEnrollment
    {
        $enrollment = $this->repository->findScoped($id, $context);
        if (! $enrollment instanceof EducationEnrollment) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'enrollment not found in current context', ['id' => $id]);
        }

        return $enrollment;
    }

    /**
     * @return array{enrollment: EducationEnrollment, account: ?EducationStudentCourseAccount}
     */
    public function create(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $student = $this->enabledStudentOrFail((int) ($data['student_id'] ?? 0), $tenantId, $campusId);
        $course = $this->enabledCourseOrFail((int) ($data['course_id'] ?? 0), $tenantId, $campusId);
        $package = $this->enabledPackageOrFail((int) ($data['lesson_package_id'] ?? 0), $tenantId, $campusId, (int) $course->id);
        $dealAmount = $this->decimal($data['deal_amount'] ?? $package->sale_price);
        $enrolledAt = isset($data['enrolled_at']) && $data['enrolled_at'] !== ''
            ? Carbon::parse((string) $data['enrolled_at'])->toDateTimeString()
            : Carbon::now()->toDateTimeString();

        return Db::transaction(function () use ($data, $tenantId, $campusId, $student, $course, $package, $dealAmount, $enrolledAt, $context, $operatorId): array {
            $enrollment = $this->repository->createPending([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'enrollment_no' => $this->repository->nextEnrollmentNo($tenantId, $campusId),
                'student_id' => (int) $student->id,
                'course_id' => (int) $course->id,
                'lesson_package_id' => (int) $package->id,
                'account_id' => null,
                'student_name_snapshot' => $student->name,
                'course_name_snapshot' => $course->name,
                'package_name_snapshot' => $package->name,
                'package_lesson_units' => $this->decimal($package->lesson_units),
                'package_bonus_units' => $this->decimal($package->bonus_units),
                'total_units' => $this->decimal($package->total_units),
                'list_price' => $this->decimal($package->list_price),
                'deal_amount' => $dealAmount,
                'status' => 'pending',
                'enrolled_at' => $enrolledAt,
                'remark' => $data['remark'] ?? null,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $this->dispatchEnrollmentAudit('created', $enrollment, $context, [], $enrollment->toArray());

            if ($this->featureFlagService->enabled(self::FINANCE_GATE, $tenantId)) {
                return ['enrollment' => $enrollment, 'account' => null];
            }

            return $this->confirmLocked($enrollment, $context, $operatorId);
        });
    }

    /**
     * @return array{enrollment: EducationEnrollment, account: EducationStudentCourseAccount}
     */
    public function confirm(int $id, EducationUserContext $context, ?int $operatorId): array
    {
        return Db::transaction(function () use ($id, $context, $operatorId): array {
            $enrollment = $this->repository->lockScoped($id, $context);
            if (! $enrollment instanceof EducationEnrollment) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'enrollment not found in current context', ['id' => $id]);
            }

            return $this->confirmLocked($enrollment, $context, $operatorId);
        });
    }

    /**
     * @return array{enrollment: EducationEnrollment, account: ?EducationStudentCourseAccount}
     */
    public function cancel(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
    {
        return Db::transaction(function () use ($id, $reason, $context, $operatorId): array {
            $enrollment = $this->repository->lockScoped($id, $context);
            if (! $enrollment instanceof EducationEnrollment) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'enrollment not found in current context', ['id' => $id]);
            }
            if ($enrollment->status === 'cancelled') {
                throw new BusinessException(ResultCode::CONFLICT, 'enrollment is already cancelled', ['id' => $id]);
            }

            $before = $enrollment->toArray();
            if ($enrollment->status === 'pending') {
                $enrollment = $this->repository->cancel((int) $enrollment->id, $reason, $operatorId);
                $this->dispatchEnrollmentAudit('cancelled', $enrollment, $context, $before, $enrollment->toArray());

                return ['enrollment' => $enrollment, 'account' => null];
            }

            $account = $this->accountService->reverseEnrollment($enrollment, $context, $operatorId);
            $enrollment = $this->repository->cancel((int) $enrollment->id, $reason, $operatorId);
            $this->dispatchEnrollmentAudit('cancelled', $enrollment, $context, $before, $enrollment->toArray());

            return ['enrollment' => $enrollment, 'account' => $account];
        });
    }

    /**
     * @return array{enrollment: EducationEnrollment, account: EducationStudentCourseAccount}
     */
    private function confirmLocked(EducationEnrollment $enrollment, EducationUserContext $context, ?int $operatorId): array
    {
        if ($enrollment->status === 'confirmed') {
            if ($enrollment->account_id === null) {
                throw new BusinessException(ResultCode::CONFLICT, 'confirmed enrollment has no account', ['id' => (int) $enrollment->id]);
            }
            $account = $this->accountService->materializeEnrollment((int) $enrollment->id, $context, $operatorId);

            return ['enrollment' => $enrollment, 'account' => $account];
        }
        if ($enrollment->status === 'cancelled') {
            throw new BusinessException(ResultCode::CONFLICT, 'cancelled enrollment cannot be confirmed', ['id' => (int) $enrollment->id]);
        }

        $before = $enrollment->toArray();
        $account = $this->accountService->materializeEnrollment((int) $enrollment->id, $context, $operatorId);
        $enrollment = $this->repository->markConfirmed((int) $enrollment->id, (int) $account->id, $operatorId);
        $this->eventDispatcher->dispatch(new EducationEnrollmentConfirmed($enrollment, $account, $context));
        $this->dispatchEnrollmentAudit('confirmed', $enrollment, $context, $before, $enrollment->toArray());

        return ['enrollment' => $enrollment, 'account' => $account];
    }

    private function enabledStudentOrFail(int $studentId, int $tenantId, int $campusId): EducationStudent
    {
        $student = EducationStudent::query()
            ->whereKey($studentId)
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('status', 'enabled')
            ->first();
        if (! $student instanceof EducationStudent) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'student is disabled', ['student_id' => $studentId]);
        }

        return $student;
    }

    private function enabledCourseOrFail(int $courseId, int $tenantId, int $campusId): EducationCourse
    {
        $course = $this->courseRepository->findEnabledForEnrollment($courseId, $tenantId, $campusId);
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'course is disabled', ['course_id' => $courseId]);
        }

        return $course;
    }

    private function enabledPackageOrFail(int $packageId, int $tenantId, int $campusId, int $courseId): EducationLessonPackage
    {
        $package = $this->packageRepository->findEnabledForEnrollment($packageId, $tenantId, $campusId, $courseId);
        if (! $package instanceof EducationLessonPackage) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson package is disabled', ['lesson_package_id' => $packageId]);
        }

        return $package;
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
        $exists = EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($campusId)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current tenant', ['campus_id' => $campusId]);
        }
        if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => $campusId]);
        }

        return $campusId;
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

    private function dispatchEnrollmentAudit(string $action, EducationEnrollment $enrollment, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'enrollment',
            action: 'education.academic.enrollment.' . $action,
            businessType: 'enrollment',
            businessId: (int) $enrollment->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $enrollment->tenant_id, 'campus_id' => (int) $enrollment->campus_id, 'student_id' => (int) $enrollment->student_id],
            summary: \sprintf('Enrollment %s %s', $enrollment->enrollment_no, $action)
        ));
    }
}

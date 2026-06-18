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
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Academic\AcademicRecordStatus;
use App\Model\Enums\Education\Academic\ClassType;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\ClassRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ClassService
{
    public function __construct(
        private readonly ClassRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationClass
    {
        $tenantId = $this->tenantIdForWrite($data, $context);
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? null, $context);
        $course = $this->enabledCourse((int) ($data['course_id'] ?? 0), $tenantId, $campusId);
        $teacherId = isset($data['main_teacher_id']) && $data['main_teacher_id'] !== '' ? (int) $data['main_teacher_id'] : null;
        $classroomId = isset($data['classroom_id']) && $data['classroom_id'] !== '' ? (int) $data['classroom_id'] : null;
        if ($teacherId !== null) {
            $this->enabledTeacher($teacherId, $tenantId, $campusId);
            $this->assertTeacherAuthorized($teacherId, (int) $course->id, $tenantId, $campusId);
        }
        if ($classroomId !== null) {
            $this->enabledClassroom($classroomId, $tenantId, $campusId);
        }
        $code = trim((string) ($data['code'] ?? ''));
        $this->assertUniqueCode($tenantId, $campusId, $code);
        $this->assertDateRange($data);

        $payload = $this->classPayload($data, $tenantId, $campusId, (int) $course->id, $teacherId, $classroomId, $operatorId);

        return Db::transaction(function () use ($payload, $context): EducationClass {
            $class = $this->repository->create($payload)->refresh();
            $this->dispatchAudit('created', $class, $context, [], $class->toArray());

            return $class;
        });
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationClass
    {
        $class = $this->findForWrite($id, $context);
        $tenantId = (int) $class->tenant_id;
        $campusId = $this->campusIdForWrite($tenantId, $data['campus_id'] ?? $class->campus_id, $context);
        $course = $this->enabledCourse((int) ($data['course_id'] ?? $class->course_id), $tenantId, $campusId);
        $teacherId = isset($data['main_teacher_id']) ? (int) $data['main_teacher_id'] : ($class->main_teacher_id === null ? null : (int) $class->main_teacher_id);
        $classroomId = isset($data['classroom_id']) ? (int) $data['classroom_id'] : ($class->classroom_id === null ? null : (int) $class->classroom_id);
        if ($teacherId !== null) {
            $this->enabledTeacher($teacherId, $tenantId, $campusId);
            $this->assertTeacherAuthorized($teacherId, (int) $course->id, $tenantId, $campusId);
        }
        if ($classroomId !== null) {
            $this->enabledClassroom($classroomId, $tenantId, $campusId);
        }
        $code = trim((string) ($data['code'] ?? $class->code));
        $this->assertUniqueCode($tenantId, $campusId, $code, $id);
        $this->assertDateRange($data + $class->toArray());
        $payload = $this->classPayload($data + $class->toArray(), $tenantId, $campusId, (int) $course->id, $teacherId, $classroomId, $operatorId);

        return Db::transaction(function () use ($class, $payload, $context): EducationClass {
            $before = $class->toArray();
            $class->fill($payload);
            $class->save();
            $class = $class->refresh();
            $this->dispatchAudit('updated', $class, $context, $before, $class->toArray());

            return $class;
        });
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationClass
    {
        $class = $this->findForWrite($id, $context);
        $class->status = $this->normalizeStatus($status);
        $class->updated_by = $operatorId;
        $class->save();

        return $class->refresh();
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $class = $this->findForWrite($id, $context);
        if ($this->repository->hasLessonReferences((int) $class->id, (int) $class->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'class is referenced by lessons', ['id' => (int) $class->id]);
        }
        $class->updated_by = $operatorId;
        $class->save();

        return (bool) $class->delete();
    }

    public function options(array $filters, EducationUserContext $context): array
    {
        return $this->repository->options($filters, $context);
    }

    private function classPayload(array $data, int $tenantId, int $campusId, int $courseId, ?int $teacherId, ?int $classroomId, ?int $operatorId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'main_teacher_id' => $teacherId,
            'classroom_id' => $classroomId,
            'code' => trim((string) ($data['code'] ?? '')),
            'name' => trim((string) ($data['name'] ?? '')),
            'class_type' => $this->normalizeClassType((string) ($data['class_type'] ?? ClassType::Group->value)),
            'max_students' => (int) ($data['max_students'] ?? 0),
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'lesson_units' => $this->decimal($data['lesson_units'] ?? '1.00'),
            'status' => $this->normalizeStatus((string) ($data['status'] ?? AcademicRecordStatus::Enabled->value)),
            'schedule_note' => $data['schedule_note'] ?? null,
            'remark' => $data['remark'] ?? null,
            'created_by' => $data['created_by'] ?? $operatorId,
            'updated_by' => $operatorId,
        ];
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationClass
    {
        $class = $this->repository->findById($id);
        if (! $class instanceof EducationClass) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'class not found', ['id' => $id]);
        }
        $this->assertTenantVisible((int) $class->tenant_id, $context);
        $this->assertCampusWritable((int) $class->tenant_id, (int) $class->campus_id, $context);

        return $class;
    }

    private function tenantIdForWrite(array $data, EducationUserContext $context): int
    {
        $tenantId = $context->platformAccess ? (int) ($data['tenant_id'] ?? 0) : (int) $context->tenantId;
        if ($tenantId === 0 || ! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }
        if (! $context->platformAccess && isset($data['tenant_id']) && (int) $data['tenant_id'] !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
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
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
        }
    }

    private function assertCampusWritable(int $tenantId, int $campusId, EducationUserContext $context): void
    {
        if (! EducationCampus::query()->where('tenant_id', $tenantId)->whereKey($campusId)->exists()) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current tenant', ['campus_id' => $campusId]);
        }
        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin || $context->canAccessCampus($campusId)) {
            return;
        }
        throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => $campusId]);
    }

    private function enabledCourse(int $courseId, int $tenantId, int $campusId): EducationCourse
    {
        $course = EducationCourse::query()->whereKey($courseId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'course is disabled', ['course_id' => $courseId]);
        }

        return $course;
    }

    private function enabledTeacher(int $teacherId, int $tenantId, int $campusId): EducationTeacher
    {
        $teacher = EducationTeacher::query()->whereKey($teacherId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $teacher instanceof EducationTeacher) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is disabled', ['teacher_id' => $teacherId]);
        }

        return $teacher;
    }

    private function enabledClassroom(int $classroomId, int $tenantId, int $campusId): EducationClassroom
    {
        $classroom = EducationClassroom::query()->whereKey($classroomId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $classroom instanceof EducationClassroom) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'classroom is disabled', ['classroom_id' => $classroomId]);
        }

        return $classroom;
    }

    private function assertTeacherAuthorized(int $teacherId, int $courseId, int $tenantId, int $campusId): void
    {
        if (! EducationTeacherCourse::query()->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('teacher_id', $teacherId)->where('course_id', $courseId)->where('status', 'enabled')->exists()) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is not authorized for course', ['teacher_id' => $teacherId, 'course_id' => $courseId]);
        }
    }

    private function assertUniqueCode(int $tenantId, int $campusId, string $code, ?int $excludeId = null): void
    {
        if ($this->repository->existsCode($tenantId, $campusId, $code, $excludeId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'class code already exists', ['campus_id' => $campusId, 'code' => $code]);
        }
    }

    private function assertDateRange(array $data): void
    {
        if (($data['start_date'] ?? null) && ($data['end_date'] ?? null) && Carbon::parse((string) $data['end_date'])->lt(Carbon::parse((string) $data['start_date']))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'class end_date must be later than start_date');
        }
    }

    private function decimal(mixed $value): string
    {
        $decimal = (float) $value;
        if ($decimal <= 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson_units must be greater than zero');
        }

        return number_format($decimal, 2, '.', '');
    }

    private function normalizeStatus(string $status): string
    {
        if (AcademicRecordStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid class status');
        }

        return $status;
    }

    private function normalizeClassType(string $classType): string
    {
        if (ClassType::tryFrom($classType) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid class type');
        }

        return $classType;
    }

    private function extractPage(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationClass $class, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'class',
            action: 'education.academic.class.' . $action,
            businessType: 'class',
            businessId: (int) $class->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $class->tenant_id, 'campus_id' => (int) $class->campus_id],
            summary: \sprintf('Class %s %s', $class->name, $action)
        ));
    }
}

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
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\LeaveRequestRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class LeaveRequestService
{
    public function __construct(
        private readonly LeaveRequestRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function detail(int $id, EducationUserContext $context): EducationLeaveRequest
    {
        return $this->findScoped($id, $context);
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
    {
        $lessonStudent = EducationLessonStudent::query()
            ->whereKey((int) $data['lesson_student_id'])
            ->where('status', '<>', 'cancelled')
            ->first();
        if (! $lessonStudent instanceof EducationLessonStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson student not found', ['lesson_student_id' => (int) $data['lesson_student_id']]);
        }
        $this->assertScope((int) $lessonStudent->tenant_id, (int) $lessonStudent->campus_id, $context);
        if ($this->repository->existsForLessonStudent((int) $lessonStudent->id, (int) $lessonStudent->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'leave request already exists for lesson student', ['lesson_student_id' => (int) $lessonStudent->id]);
        }
        $lesson = $this->lesson((int) $lessonStudent->lesson_id, (int) $lessonStudent->tenant_id);
        if ($lesson->status === 'cancelled') {
            throw new BusinessException(ResultCode::CONFLICT, 'cancelled lesson cannot request leave', ['lesson_id' => (int) $lesson->id]);
        }
        $account = $this->activeAccount((int) $lessonStudent->account_id, (int) $lessonStudent->tenant_id);
        $this->assertSource($data, $lesson, $lessonStudent);

        return Db::transaction(function () use ($data, $lesson, $lessonStudent, $account, $context, $operatorId): EducationLeaveRequest {
            $leave = $this->repository->createRequest([
                'tenant_id' => (int) $lessonStudent->tenant_id,
                'campus_id' => (int) $lessonStudent->campus_id,
                'leave_no' => $this->nextLeaveNo((int) $lessonStudent->tenant_id, (int) $lessonStudent->campus_id),
                'source' => (string) $data['source'],
                'leave_type' => (string) $data['leave_type'],
                'lesson_id' => (int) $lesson->id,
                'lesson_student_id' => (int) $lessonStudent->id,
                'class_id' => (int) $lessonStudent->class_id,
                'course_id' => (int) $lessonStudent->course_id,
                'student_id' => (int) $lessonStudent->student_id,
                'account_id' => (int) $account->id,
                'guardian_id' => isset($data['guardian_id']) && $data['guardian_id'] !== '' ? (int) $data['guardian_id'] : null,
                'teacher_id' => isset($data['teacher_id']) && $data['teacher_id'] !== '' ? (int) $data['teacher_id'] : (int) $lesson->teacher_id,
                'reason' => trim((string) $data['reason']),
                'status' => 'pending',
                'requested_at' => Carbon::now()->toDateTimeString(),
                'makeup_required' => (bool) ($data['makeup_required'] ?? true),
                'remark' => $data['remark'] ?? null,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $this->dispatchAudit('created', $leave, $context, [], $leave->toArray());

            return $leave;
        });
    }

    public function approve(int $id, string $reviewRemark, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
    {
        $this->assertCanReview($context);
        $leave = $this->findScoped($id, $context);
        $this->assertPending($leave);
        if ($this->hasActiveConsumption((int) $leave->lesson_student_id, (int) $leave->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson student already has active consumption', ['lesson_student_id' => (int) $leave->lesson_student_id]);
        }

        return $this->review($leave, 'approved', $reviewRemark, $context, $operatorId);
    }

    public function reject(int $id, string $reviewRemark, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
    {
        $this->assertCanReview($context);
        $leave = $this->findScoped($id, $context);
        $this->assertPending($leave);

        return $this->review($leave, 'rejected', $reviewRemark, $context, $operatorId);
    }

    public function cancel(int $id, string $cancelReason, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
    {
        $leave = $this->findScoped($id, $context);
        if (! \in_array($leave->status, ['pending', 'approved'], true)) {
            throw new BusinessException(ResultCode::CONFLICT, 'leave request cannot be cancelled in current status', ['id' => $id, 'status' => $leave->status]);
        }

        $before = $leave->toArray();
        $cancelled = $this->repository->updateStatus($id, 'cancelled', [
            'cancelled_at' => Carbon::now()->toDateTimeString(),
            'cancelled_by' => $operatorId,
            'cancel_reason' => $cancelReason,
        ], $operatorId);
        $this->dispatchAudit('cancelled', $cancelled, $context, $before, $cancelled->toArray());

        return $cancelled;
    }

    private function findScoped(int $id, EducationUserContext $context): EducationLeaveRequest
    {
        $leave = $this->repository->findScoped($id, $context);
        if (! $leave instanceof EducationLeaveRequest) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'leave request not found in current context', ['id' => $id]);
        }

        return $leave;
    }

    private function assertScope(int $tenantId, int $campusId, EducationUserContext $context): void
    {
        if ($context->platformAccess) {
            return;
        }
        if ($context->tenantId !== $tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current context', ['tenant_id' => $tenantId]);
        }
        if ($context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current context', ['campus_id' => $campusId]);
        }
    }

    private function assertSource(array $data, EducationLesson $lesson, EducationLessonStudent $lessonStudent): void
    {
        if (($data['source'] ?? '') === 'guardian') {
            $guardianId = (int) ($data['guardian_id'] ?? 0);
            if ($guardianId <= 0 || ! EducationStudentGuardian::query()->where('tenant_id', $lessonStudent->tenant_id)->where('student_id', $lessonStudent->student_id)->where('guardian_id', $guardianId)->exists()) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'guardian is not bound to student', ['guardian_id' => $guardianId]);
            }
        }
        if (($data['source'] ?? '') === 'teacher') {
            $teacherId = (int) ($data['teacher_id'] ?? 0);
            if ($teacherId <= 0 || $teacherId !== (int) $lesson->teacher_id) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is not assigned to lesson', ['teacher_id' => $teacherId]);
            }
        }
    }

    private function assertPending(EducationLeaveRequest $leave): void
    {
        if ($leave->status !== 'pending') {
            throw new BusinessException(ResultCode::CONFLICT, 'only pending leave request can be reviewed', ['id' => (int) $leave->id, 'status' => $leave->status]);
        }
    }

    private function assertCanReview(EducationUserContext $context): void
    {
        if ($context->roleCode === EducationRoleCode::FrontDesk) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'front desk cannot review leave requests');
        }
    }

    private function review(EducationLeaveRequest $leave, string $status, string $reviewRemark, EducationUserContext $context, ?int $operatorId): EducationLeaveRequest
    {
        $before = $leave->toArray();
        $reviewed = $this->repository->updateStatus((int) $leave->id, $status, [
            'reviewed_at' => Carbon::now()->toDateTimeString(),
            'reviewed_by' => $operatorId,
            'review_remark' => $reviewRemark,
        ], $operatorId);
        $this->dispatchAudit($status, $reviewed, $context, $before, $reviewed->toArray());

        return $reviewed;
    }

    private function lesson(int $lessonId, int $tenantId): EducationLesson
    {
        $lesson = EducationLesson::query()->whereKey($lessonId)->where('tenant_id', $tenantId)->first();
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found', ['lesson_id' => $lessonId]);
        }

        return $lesson;
    }

    private function activeAccount(int $accountId, int $tenantId): EducationStudentCourseAccount
    {
        $account = EducationStudentCourseAccount::query()->whereKey($accountId)->where('tenant_id', $tenantId)->first();
        if (! $account instanceof EducationStudentCourseAccount || $account->status !== 'active') {
            throw new BusinessException(ResultCode::CONFLICT, 'student course account is not active', ['account_id' => $accountId]);
        }

        return $account;
    }

    private function hasActiveConsumption(int $lessonStudentId, int $tenantId): bool
    {
        return EducationLessonConsumption::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_student_id', $lessonStudentId)
            ->where('status', 'active')
            ->exists();
    }

    private function nextLeaveNo(int $tenantId, int $campusId): string
    {
        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $leaveNo = $this->repository->nextLeaveNo($tenantId, $campusId);
            if (! EducationLeaveRequest::query()->where('tenant_id', $tenantId)->where('leave_no', $leaveNo)->exists()) {
                return $leaveNo;
            }
        }

        throw new BusinessException(ResultCode::CONFLICT, 'leave_no generation collision');
    }

    private function dispatchAudit(string $action, EducationLeaveRequest $leave, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'leave_request',
            action: 'education.academic.leave_request.' . $action,
            businessType: 'leave_request',
            businessId: (int) $leave->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $leave->tenant_id, 'campus_id' => (int) $leave->campus_id],
            summary: 'Leave request ' . $action
        ));
    }
}

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
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\LeaveRequestRepository;
use App\Repository\Education\Academic\LessonChangeRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class MakeupLessonService
{
    public function __construct(
        private readonly LeaveRequestRepository $leaveRequestRepository,
        private readonly LessonChangeRepository $lessonChangeRepository,
        private readonly SchedulingConflictService $conflictService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function create(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        if ($context->roleCode === EducationRoleCode::FrontDesk) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'front desk cannot create make-up lessons');
        }
        $leave = $this->leaveRequest((int) $data['leave_request_id'], $context);

        return $this->createMakeupLesson($leave, $data, $context, $operatorId);
    }

    private function createMakeupLesson(EducationLeaveRequest $leaveRequest, array $data, EducationUserContext $context, ?int $operatorId): array
    {
        if ($leaveRequest->status !== 'approved') {
            throw new BusinessException(ResultCode::CONFLICT, 'make-up creation requires approved leave', ['leave_request_id' => (int) $leaveRequest->id, 'status' => $leaveRequest->status]);
        }
        if ($leaveRequest->makeup_lesson_id !== null || $this->lessonChangeRepository->hasMakeupForLeave((int) $leaveRequest->id, (int) $leaveRequest->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'make-up lesson already exists for leave request', ['leave_request_id' => (int) $leaveRequest->id]);
        }
        $sourceLesson = $this->lesson((int) $leaveRequest->lesson_id, (int) $leaveRequest->tenant_id);
        $sourceLessonStudent = $this->lessonStudent((int) $leaveRequest->lesson_student_id, (int) $leaveRequest->tenant_id);
        $teacher = $this->teacher((int) $data['teacher_id'], (int) $sourceLesson->tenant_id, (int) $sourceLesson->campus_id, (int) $sourceLesson->course_id);
        $classroom = isset($data['classroom_id']) && $data['classroom_id'] !== '' ? $this->classroom((int) $data['classroom_id'], (int) $sourceLesson->tenant_id, (int) $sourceLesson->campus_id) : null;
        $account = $this->activeAccount((int) $sourceLessonStudent->account_id, (int) $sourceLesson->tenant_id);
        if ((int) $account->id !== (int) $leaveRequest->account_id) {
            throw new BusinessException(ResultCode::CONFLICT, 'leave request account does not match lesson student account', ['account_id' => (int) $account->id]);
        }
        [$startAt, $endAt, $durationMinutes, $lessonUnits] = $this->timeAndUnits($data);
        $this->conflictService->assertNoConflict([
            'tenant_id' => (int) $sourceLesson->tenant_id,
            'campus_id' => (int) $sourceLesson->campus_id,
            'class_id' => (int) $sourceLesson->class_id,
            'teacher_id' => (int) $teacher->id,
            'classroom_id' => $classroom?->id,
            'student_ids' => [(int) $sourceLessonStudent->student_id],
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        return Db::transaction(function () use ($leaveRequest, $sourceLesson, $sourceLessonStudent, $teacher, $classroom, $startAt, $endAt, $durationMinutes, $lessonUnits, $data, $context, $operatorId): array {
            $targetLesson = EducationLesson::query()->create([
                'tenant_id' => (int) $sourceLesson->tenant_id,
                'campus_id' => (int) $sourceLesson->campus_id,
                'lesson_no' => $this->nextLessonNo((int) $sourceLesson->tenant_id, (int) $sourceLesson->campus_id),
                'class_id' => (int) $sourceLesson->class_id,
                'course_id' => (int) $sourceLesson->course_id,
                'teacher_id' => (int) $teacher->id,
                'classroom_id' => $classroom?->id,
                'title' => trim((string) $data['title']),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration_minutes' => $durationMinutes,
                'lesson_units' => $lessonUnits,
                'student_count' => 1,
                'status' => 'scheduled',
                'source_type' => 'manual',
                'class_name_snapshot' => $sourceLesson->class_name_snapshot,
                'course_name_snapshot' => $sourceLesson->course_name_snapshot,
                'teacher_name_snapshot' => $teacher->name,
                'classroom_name_snapshot' => $classroom?->name,
                'remark' => $data['reason'],
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $targetLessonStudent = EducationLessonStudent::query()->create([
                'tenant_id' => (int) $sourceLessonStudent->tenant_id,
                'campus_id' => (int) $sourceLessonStudent->campus_id,
                'lesson_id' => (int) $targetLesson->id,
                'class_id' => (int) $sourceLessonStudent->class_id,
                'course_id' => (int) $sourceLessonStudent->course_id,
                'student_id' => (int) $sourceLessonStudent->student_id,
                'account_id' => (int) $sourceLessonStudent->account_id,
                'student_name_snapshot' => $sourceLessonStudent->student_name_snapshot,
                'student_no_snapshot' => $sourceLessonStudent->student_no_snapshot,
                'lesson_units' => $lessonUnits,
                'status' => 'planned',
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $changeRecord = $this->lessonChangeRepository->createRecord([
                'tenant_id' => (int) $sourceLesson->tenant_id,
                'campus_id' => (int) $sourceLesson->campus_id,
                'change_no' => $this->lessonChangeRepository->nextChangeNo((int) $sourceLesson->tenant_id, (int) $sourceLesson->campus_id),
                'change_type' => 'makeup',
                'status' => 'confirmed',
                'leave_request_id' => (int) $leaveRequest->id,
                'source_lesson_id' => (int) $sourceLesson->id,
                'source_lesson_student_id' => (int) $sourceLessonStudent->id,
                'target_lesson_id' => (int) $targetLesson->id,
                'class_id' => (int) $sourceLesson->class_id,
                'course_id' => (int) $sourceLesson->course_id,
                'student_id' => (int) $sourceLessonStudent->student_id,
                'account_id' => (int) $sourceLessonStudent->account_id,
                'source_teacher_id' => (int) $sourceLesson->teacher_id,
                'target_teacher_id' => (int) $teacher->id,
                'source_classroom_id' => $sourceLesson->classroom_id,
                'target_classroom_id' => $classroom?->id,
                'source_start_at' => $sourceLesson->start_at?->toDateTimeString(),
                'source_end_at' => $sourceLesson->end_at?->toDateTimeString(),
                'target_start_at' => $startAt,
                'target_end_at' => $endAt,
                'lesson_units' => $lessonUnits,
                'reason' => trim((string) $data['reason']),
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $updatedLeave = $this->leaveRequestRepository->updateStatus((int) $leaveRequest->id, 'makeup_scheduled', [
                'makeup_lesson_id' => (int) $targetLesson->id,
            ], $operatorId);
            $this->dispatchAudit($changeRecord, $context, $updatedLeave->toArray());

            return [
                'leave_request' => $updatedLeave,
                'target_lesson' => $targetLesson,
                'target_lesson_student' => $targetLessonStudent,
                'change_record' => $changeRecord,
            ];
        });
    }

    private function leaveRequest(int $id, EducationUserContext $context): EducationLeaveRequest
    {
        $leave = $this->leaveRequestRepository->findScoped($id, $context);
        if (! $leave instanceof EducationLeaveRequest) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'leave request not found in current context', ['id' => $id]);
        }

        return $leave;
    }

    private function lesson(int $id, int $tenantId): EducationLesson
    {
        $lesson = EducationLesson::query()->whereKey($id)->where('tenant_id', $tenantId)->first();
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found', ['lesson_id' => $id]);
        }

        return $lesson;
    }

    private function lessonStudent(int $id, int $tenantId): EducationLessonStudent
    {
        $lessonStudent = EducationLessonStudent::query()->whereKey($id)->where('tenant_id', $tenantId)->where('status', '<>', 'cancelled')->first();
        if (! $lessonStudent instanceof EducationLessonStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson student not found', ['lesson_student_id' => $id]);
        }

        return $lessonStudent;
    }

    private function teacher(int $teacherId, int $tenantId, int $campusId, int $courseId): EducationTeacher
    {
        $teacher = EducationTeacher::query()->whereKey($teacherId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $teacher instanceof EducationTeacher) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is disabled', ['teacher_id' => $teacherId]);
        }
        if (! EducationTeacherCourse::query()->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('teacher_id', $teacherId)->where('course_id', $courseId)->where('status', 'enabled')->exists()) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is not authorized for course', ['teacher_id' => $teacherId]);
        }

        return $teacher;
    }

    private function classroom(int $classroomId, int $tenantId, int $campusId): EducationClassroom
    {
        $classroom = EducationClassroom::query()->whereKey($classroomId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $classroom instanceof EducationClassroom) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'classroom is disabled', ['classroom_id' => $classroomId]);
        }

        return $classroom;
    }

    private function activeAccount(int $accountId, int $tenantId): EducationStudentCourseAccount
    {
        $account = EducationStudentCourseAccount::query()->whereKey($accountId)->where('tenant_id', $tenantId)->first();
        if (! $account instanceof EducationStudentCourseAccount || $account->status !== 'active') {
            throw new BusinessException(ResultCode::CONFLICT, 'student course account is not active', ['account_id' => $accountId]);
        }

        return $account;
    }

    private function timeAndUnits(array $data): array
    {
        $startAt = Carbon::parse((string) $data['start_at'])->toDateTimeString();
        $endAt = Carbon::parse((string) $data['end_at'])->toDateTimeString();
        if (! Carbon::parse($endAt)->gt(Carbon::parse($startAt))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson end_at must be later than start_at');
        }
        $lessonUnits = number_format((float) $data['lesson_units'], 2, '.', '');
        if ((float) $lessonUnits <= 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson_units must be greater than zero');
        }

        return [$startAt, $endAt, Carbon::parse($startAt)->diffInMinutes(Carbon::parse($endAt)), $lessonUnits];
    }

    private function nextLessonNo(int $tenantId, int $campusId): string
    {
        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $lessonNo = 'LES' . date('YmdHis') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . str_pad((string) $campusId, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
            if (! EducationLesson::query()->where('tenant_id', $tenantId)->where('lesson_no', $lessonNo)->exists()) {
                return $lessonNo;
            }
        }

        throw new BusinessException(ResultCode::CONFLICT, 'lesson_no generation collision');
    }

    private function dispatchAudit(mixed $changeRecord, EducationUserContext $context, array $leaveSnapshot): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'lesson_change',
            action: 'education.academic.lesson_change.makeup_created',
            businessType: 'lesson_change',
            businessId: (int) $changeRecord->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['leave_request' => $leaveSnapshot, 'change_record' => $changeRecord->toArray()],
            metadata: ['tenant_id' => (int) $changeRecord->tenant_id, 'campus_id' => (int) $changeRecord->campus_id],
            summary: 'Make-up lesson created'
        ));
    }
}

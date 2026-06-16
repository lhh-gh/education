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
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Repository\Education\Academic\LessonChangeRepository;
use App\Repository\Education\Academic\LessonRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class RescheduleService
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
        private readonly LessonChangeRepository $lessonChangeRepository,
        private readonly SchedulingConflictService $conflictService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function reschedule(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        $lesson = $this->sourceLesson((int) $data['source_lesson_id'], $context);
        if ($lesson->status !== 'scheduled') {
            throw new BusinessException(ResultCode::CONFLICT, 'only scheduled lessons can be rescheduled', ['lesson_id' => (int) $lesson->id, 'status' => $lesson->status]);
        }
        if (EducationLessonAttendance::query()->where('tenant_id', $lesson->tenant_id)->where('lesson_id', $lesson->id)->exists()
            || EducationLessonConsumption::query()->where('tenant_id', $lesson->tenant_id)->where('lesson_id', $lesson->id)->exists()) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson already has attendance or consumption', ['lesson_id' => (int) $lesson->id]);
        }
        $teacher = $this->teacher((int) $data['teacher_id'], (int) $lesson->tenant_id, (int) $lesson->campus_id, (int) $lesson->course_id);
        $classroom = isset($data['classroom_id']) && $data['classroom_id'] !== '' ? $this->classroom((int) $data['classroom_id'], (int) $lesson->tenant_id, (int) $lesson->campus_id) : null;
        [$startAt, $endAt, $durationMinutes, $lessonUnits] = $this->timeAndUnits($data);
        $lessonStudents = EducationLessonStudent::query()
            ->where('tenant_id', $lesson->tenant_id)
            ->where('lesson_id', $lesson->id)
            ->where('status', 'planned')
            ->orderBy('id')
            ->get()
            ->all();
        $studentIds = array_map(static fn (EducationLessonStudent $row): int => (int) $row->student_id, $lessonStudents);
        $this->conflictService->assertNoConflict([
            'tenant_id' => (int) $lesson->tenant_id,
            'campus_id' => (int) $lesson->campus_id,
            'class_id' => (int) $lesson->class_id,
            'teacher_id' => (int) $teacher->id,
            'classroom_id' => $classroom?->id,
            'student_ids' => $studentIds,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'exclude_lesson_id' => (int) $lesson->id,
        ]);

        return Db::transaction(function () use ($lesson, $teacher, $classroom, $startAt, $endAt, $durationMinutes, $lessonUnits, $data, $context, $operatorId): array {
            $before = $lesson->toArray();
            $updated = $this->lessonRepository->updateScheduled((int) $lesson->id, [
                'teacher_id' => (int) $teacher->id,
                'classroom_id' => $classroom?->id,
                'title' => trim((string) $data['title']),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration_minutes' => $durationMinutes,
                'lesson_units' => $lessonUnits,
                'teacher_name_snapshot' => $teacher->name,
                'classroom_name_snapshot' => $classroom?->name,
            ], $operatorId);
            EducationLessonStudent::query()
                ->where('tenant_id', $lesson->tenant_id)
                ->where('lesson_id', $lesson->id)
                ->where('status', 'planned')
                ->update([
                    'lesson_units' => $lessonUnits,
                    'updated_by' => $operatorId,
                ]);
            $changeRecord = $this->lessonChangeRepository->createRecord([
                'tenant_id' => (int) $lesson->tenant_id,
                'campus_id' => (int) $lesson->campus_id,
                'change_no' => $this->lessonChangeRepository->nextChangeNo((int) $lesson->tenant_id, (int) $lesson->campus_id),
                'change_type' => 'reschedule',
                'status' => 'confirmed',
                'source_lesson_id' => (int) $lesson->id,
                'target_lesson_id' => (int) $lesson->id,
                'class_id' => (int) $lesson->class_id,
                'course_id' => (int) $lesson->course_id,
                'source_teacher_id' => (int) $lesson->teacher_id,
                'target_teacher_id' => (int) $teacher->id,
                'source_classroom_id' => $lesson->classroom_id,
                'target_classroom_id' => $classroom?->id,
                'source_start_at' => $lesson->start_at?->toDateTimeString(),
                'source_end_at' => $lesson->end_at?->toDateTimeString(),
                'target_start_at' => $startAt,
                'target_end_at' => $endAt,
                'lesson_units' => $lessonUnits,
                'reason' => trim((string) $data['reason']),
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
            $this->dispatchAudit($changeRecord, $context, $before, $updated->toArray());

            return ['lesson' => $updated, 'change_record' => $changeRecord];
        });
    }

    private function sourceLesson(int $id, EducationUserContext $context): EducationLesson
    {
        $lesson = $this->lessonRepository->findScoped($id, $context);
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found in current context', ['lesson_id' => $id]);
        }

        return $lesson;
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

    private function dispatchAudit(mixed $changeRecord, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'lesson_change',
            action: 'education.academic.lesson_change.rescheduled',
            businessType: 'lesson_change',
            businessId: (int) $changeRecord->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: ['lesson' => $after, 'change_record' => $changeRecord->toArray()],
            metadata: ['tenant_id' => (int) $changeRecord->tenant_id, 'campus_id' => (int) $changeRecord->campus_id],
            summary: 'Lesson rescheduled'
        ));
    }
}

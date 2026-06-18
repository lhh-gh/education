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
use App\Repository\Education\Academic\ClassRepository;
use App\Repository\Education\Academic\ClassStudentRepository;
use App\Repository\Education\Academic\LessonRepository;
use App\Repository\Education\Academic\LessonStudentRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class SchedulingService
{
    public function __construct(
        private readonly ClassRepository $classRepository,
        private readonly ClassStudentRepository $classStudentRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly LessonStudentRepository $lessonStudentRepository,
        private readonly SchedulingConflictService $conflictService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function calendar(array $filters, EducationUserContext $context): array
    {
        return $this->lessonRepository->calendar($filters, $context);
    }

    public function conflictCheck(array $data, EducationUserContext $context): array
    {
        $class = $this->classForScheduling((int) $data['class_id'], $context);
        $snapshots = $this->classStudentRepository->activeStudentsByClass((int) $class->id, (int) $class->tenant_id, (int) $class->campus_id);

        return $this->conflictService->checkSingle($this->conflictPayload($data, $class, $snapshots));
    }

    public function scheduleSingle(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        $class = $this->classForScheduling((int) $data['class_id'], $context);
        $teacher = $this->teacherForScheduling((int) ($data['teacher_id'] ?? $class->main_teacher_id), $class);
        $classroom = isset($data['classroom_id']) && $data['classroom_id'] !== '' ? $this->classroomForScheduling((int) $data['classroom_id'], $class) : ($class->classroom_id === null ? null : $this->classroomForScheduling((int) $class->classroom_id, $class));
        $course = $this->course((int) $class->course_id, (int) $class->tenant_id, (int) $class->campus_id);
        $snapshots = $this->classStudentRepository->activeStudentsByClass((int) $class->id, (int) $class->tenant_id, (int) $class->campus_id);
        if ($snapshots === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'class has no active students', ['class_id' => (int) $class->id]);
        }
        $this->assertTime((string) $data['start_at'], (string) $data['end_at']);
        $lessonUnits = $this->decimal($data['lesson_units'] ?? $class->lesson_units);
        $payload = $this->lessonPayload($data, $class, $course, $teacher, $classroom, $snapshots, $lessonUnits, 'manual', null, $operatorId);
        $this->conflictService->assertNoConflict($this->conflictPayload($payload, $class, $snapshots));

        return Db::transaction(function () use ($payload, $snapshots, $lessonUnits, $operatorId, $context): array {
            $lesson = $this->lessonRepository->createScheduled($payload)->refresh();
            $studentRows = $this->snapshotRows((int) $lesson->id, $snapshots, $lessonUnits, $operatorId);
            $students = $this->lessonStudentRepository->bulkCreateSnapshots((int) $lesson->id, $studentRows);
            $this->dispatchAudit('scheduled', $lesson, $context);

            return ['lesson' => $lesson, 'students' => $students];
        });
    }

    public function scheduleBatch(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        $class = $this->classForScheduling((int) $data['class_id'], $context);
        $teacher = $this->teacherForScheduling((int) ($data['teacher_id'] ?? $class->main_teacher_id), $class);
        $classroom = isset($data['classroom_id']) && $data['classroom_id'] !== '' ? $this->classroomForScheduling((int) $data['classroom_id'], $class) : ($class->classroom_id === null ? null : $this->classroomForScheduling((int) $class->classroom_id, $class));
        $course = $this->course((int) $class->course_id, (int) $class->tenant_id, (int) $class->campus_id);
        $snapshots = $this->classStudentRepository->activeStudentsByClass((int) $class->id, (int) $class->tenant_id, (int) $class->campus_id);
        if ($snapshots === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'class has no active students', ['class_id' => (int) $class->id]);
        }
        $generated = $this->generateBatchLessonPayloads($data);
        if (\count($generated) > 180) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'batch schedule supports up to 180 lessons');
        }
        $batchNo = 'B' . date('YmdHis') . random_int(1000, 9999);
        $studentIds = array_map(static fn (array $row): int => (int) $row['student_id'], $snapshots);
        $payloads = [];
        foreach ($generated as $row) {
            $lessonUnits = $this->decimal($data['lesson_units'] ?? $class->lesson_units);
            $lessonPayload = $this->lessonPayload($row + $data, $class, $course, $teacher, $classroom, $snapshots, $lessonUnits, 'batch', $batchNo, $operatorId);
            $payloads[] = $lessonPayload + ['student_ids' => $studentIds];
        }
        $conflicts = $this->conflictService->checkBatch($payloads);
        if ($conflicts['has_conflict']) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson schedule conflict', $conflicts);
        }

        return Db::transaction(function () use ($payloads, $snapshots, $operatorId, $context): array {
            $lessons = [];
            foreach ($payloads as $payload) {
                $lesson = $this->lessonRepository->createScheduled($payload)->refresh();
                $this->lessonStudentRepository->bulkCreateSnapshots((int) $lesson->id, $this->snapshotRows((int) $lesson->id, $snapshots, (string) $payload['lesson_units'], $operatorId));
                $this->dispatchAudit('batch_scheduled', $lesson, $context);
                $lessons[] = $lesson;
            }

            return ['lessons' => $lessons, 'total' => \count($lessons)];
        });
    }

    public function generateBatchLessonPayloads(array $data): array
    {
        $start = Carbon::parse((string) $data['start_date'])->startOfDay();
        $end = Carbon::parse((string) $data['end_date'])->startOfDay();
        if ($end->lt($start)) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'batch end_date must be later than start_date');
        }
        $weekdays = array_map('intval', $data['weekdays'] ?? []);
        $rows = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (! \in_array($date->dayOfWeekIso, $weekdays, true)) {
                continue;
            }
            $rows[] = [
                'start_at' => $date->toDateString() . ' ' . (string) $data['start_time'] . ':00',
                'end_at' => $date->toDateString() . ' ' . (string) $data['end_time'] . ':00',
            ];
        }

        return $rows;
    }

    public function buildLessonSnapshots(EducationClass $class, string $lessonUnits, ?int $operatorId): array
    {
        return $this->snapshotRows(0, $this->classStudentRepository->activeStudentsByClass((int) $class->id, (int) $class->tenant_id, (int) $class->campus_id), $lessonUnits, $operatorId);
    }

    private function classForScheduling(int $classId, EducationUserContext $context): EducationClass
    {
        $class = $this->classRepository->findScoped($classId, $context);
        if (! $class instanceof EducationClass || $class->status !== 'enabled') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'class is disabled', ['class_id' => $classId]);
        }

        return $class;
    }

    private function teacherForScheduling(int $teacherId, EducationClass $class): EducationTeacher
    {
        $teacher = EducationTeacher::query()->whereKey($teacherId)->where('tenant_id', $class->tenant_id)->where('campus_id', $class->campus_id)->where('status', 'enabled')->first();
        if (! $teacher instanceof EducationTeacher) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is disabled', ['teacher_id' => $teacherId]);
        }
        if (! EducationTeacherCourse::query()->where('tenant_id', $class->tenant_id)->where('campus_id', $class->campus_id)->where('teacher_id', $teacherId)->where('course_id', $class->course_id)->where('status', 'enabled')->exists()) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is not authorized for course', ['teacher_id' => $teacherId]);
        }

        return $teacher;
    }

    private function classroomForScheduling(int $classroomId, EducationClass $class): EducationClassroom
    {
        $classroom = EducationClassroom::query()->whereKey($classroomId)->where('tenant_id', $class->tenant_id)->where('campus_id', $class->campus_id)->where('status', 'enabled')->first();
        if (! $classroom instanceof EducationClassroom) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'classroom is disabled', ['classroom_id' => $classroomId]);
        }

        return $classroom;
    }

    private function course(int $courseId, int $tenantId, int $campusId): EducationCourse
    {
        $course = EducationCourse::query()->whereKey($courseId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'course is disabled', ['course_id' => $courseId]);
        }

        return $course;
    }

    private function lessonPayload(array $data, EducationClass $class, EducationCourse $course, EducationTeacher $teacher, ?EducationClassroom $classroom, array $snapshots, string $lessonUnits, string $sourceType, ?string $batchNo, ?int $operatorId): array
    {
        $this->assertTime((string) $data['start_at'], (string) $data['end_at']);

        return [
            'tenant_id' => (int) $class->tenant_id,
            'campus_id' => (int) $class->campus_id,
            'lesson_no' => $this->lessonRepository->nextLessonNo((int) $class->tenant_id, (int) $class->campus_id),
            'class_id' => (int) $class->id,
            'course_id' => (int) $course->id,
            'teacher_id' => (int) $teacher->id,
            'classroom_id' => $classroom === null ? null : (int) $classroom->id,
            'title' => trim((string) ($data['title'] ?? $class->name)),
            'start_at' => Carbon::parse((string) $data['start_at'])->toDateTimeString(),
            'end_at' => Carbon::parse((string) $data['end_at'])->toDateTimeString(),
            'duration_minutes' => Carbon::parse((string) $data['start_at'])->diffInMinutes(Carbon::parse((string) $data['end_at'])),
            'lesson_units' => $lessonUnits,
            'student_count' => \count($snapshots),
            'status' => 'scheduled',
            'source_type' => $sourceType,
            'schedule_batch_no' => $batchNo,
            'class_name_snapshot' => $class->name,
            'course_name_snapshot' => $course->name,
            'teacher_name_snapshot' => $teacher->name,
            'classroom_name_snapshot' => $classroom?->name,
            'remark' => $data['remark'] ?? null,
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ];
    }

    private function conflictPayload(array $data, EducationClass $class, array $snapshots): array
    {
        return [
            'tenant_id' => (int) $class->tenant_id,
            'campus_id' => (int) $class->campus_id,
            'class_id' => (int) $class->id,
            'teacher_id' => (int) $data['teacher_id'],
            'classroom_id' => $data['classroom_id'] ?? null,
            'student_ids' => array_map(static fn (array $row): int => (int) $row['student_id'], $snapshots),
            'start_at' => (string) $data['start_at'],
            'end_at' => (string) $data['end_at'],
            'exclude_lesson_id' => $data['exclude_lesson_id'] ?? null,
        ];
    }

    private function snapshotRows(int $lessonId, array $snapshots, string $lessonUnits, ?int $operatorId): array
    {
        $rows = [];
        foreach ($snapshots as $snapshot) {
            $rows[] = [
                'lesson_id' => $lessonId,
                'tenant_id' => (int) $snapshot['tenant_id'],
                'campus_id' => (int) $snapshot['campus_id'],
                'class_id' => (int) $snapshot['class_id'],
                'course_id' => (int) $snapshot['course_id'],
                'student_id' => (int) $snapshot['student_id'],
                'account_id' => (int) $snapshot['account_id'],
                'student_name_snapshot' => $snapshot['student_name_snapshot'],
                'student_no_snapshot' => $snapshot['student_no_snapshot'],
                'lesson_units' => $lessonUnits,
                'status' => 'planned',
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ];
        }

        return $rows;
    }

    private function assertTime(string $startAt, string $endAt): void
    {
        if (! Carbon::parse($endAt)->gt(Carbon::parse($startAt))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson end_at must be later than start_at');
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

    private function dispatchAudit(string $action, mixed $lesson, EducationUserContext $context): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'lesson',
            action: 'education.academic.lesson.' . $action,
            businessType: 'lesson',
            businessId: (int) $lesson->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $lesson->toArray(),
            metadata: ['tenant_id' => (int) $lesson->tenant_id, 'campus_id' => (int) $lesson->campus_id],
            summary: 'Lesson ' . $action
        ));
    }
}

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
use App\Model\Education\Academic\EducationLesson;
use App\Repository\Education\Academic\TeacherMobileLessonRepository;
use App\Schema\Education\Academic\TeacherMobileAttendanceResultSchema;
use App\Schema\Education\Academic\TeacherMobileAttendanceSheetSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TeacherMobileAttendanceService
{
    public function __construct(
        private readonly TeacherMobileContextResolver $contextResolver,
        private readonly TeacherMobileLessonRepository $lessonRepository,
        private readonly AttendanceService $attendanceService,
        private readonly TeacherMobileAttendanceSheetSchema $sheetSchema,
        private readonly TeacherMobileAttendanceResultSchema $resultSchema,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function sheet(int $lessonId, array $params, EducationUserContext $context): array
    {
        $lesson = $this->assignedLesson($lessonId, $params, $context);
        if ((string) $lesson->status === 'cancelled') {
            throw new BusinessException(ResultCode::CONFLICT, 'cancelled lesson cannot build attendance sheet', ['lesson_id' => $lessonId, 'status' => 'cancelled']);
        }

        return $this->sheetSchema->sheet($lesson, $this->sheetRecords($lesson, $context));
    }

    public function submit(int $lessonId, array $payload, EducationUserContext $context): array
    {
        $lesson = $this->assignedLesson($lessonId, $payload, $context);
        if ((string) $lesson->status === 'cancelled') {
            throw new BusinessException(ResultCode::CONFLICT, 'cancelled lesson cannot submit attendance', ['lesson_id' => $lessonId]);
        }

        $lessonStudents = $this->lessonRepository->listLessonStudents($lessonId, (int) $context->tenantId);
        if ($lessonStudents === []) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson has no active lesson students', ['lesson_id' => $lessonId]);
        }

        $normalized = $this->normalizeSubmittedRecords($payload['records'] ?? [], $lessonStudents);
        $existing = $this->lessonRepository->listAttendanceRows($lessonId, (int) $context->tenantId);
        if ($existing !== []) {
            $this->assertSameExistingPayload($lessonId, $normalized, $existing);
        }

        $summary = $this->attendanceService->submit(
            lessonId: $lessonId,
            records: array_values($normalized),
            submittedAt: $payload['submitted_at'] ?? null,
            context: $context,
            operatorId: $this->contextResolver->currentOperatorId($context)
        );

        $attendanceRows = $this->attendanceRowsWithStudents($lessonId, (int) $context->tenantId);
        $this->dispatchAudit($lesson, $context, $summary);

        return $this->resultSchema->result($lesson->refresh(), $summary, $attendanceRows);
    }

    public function result(int $lessonId, array $params, EducationUserContext $context): array
    {
        $lesson = $this->assignedLesson($lessonId, $params, $context);
        $attendanceRows = $this->attendanceRowsWithStudents($lessonId, (int) $context->tenantId);
        if ($attendanceRows === []) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'attendance result not found', ['lesson_id' => $lessonId]);
        }

        return $this->resultSchema->result($lesson, [], $attendanceRows);
    }

    private function assignedLesson(int $lessonId, array $params, EducationUserContext $context): EducationLesson
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        $lesson = $this->lessonRepository->findAssignedLesson($lessonId, $context, (int) $teacher->id, $campusId);
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found in current teacher context', ['lesson_id' => $lessonId]);
        }

        return $lesson;
    }

    private function sheetRecords(EducationLesson $lesson, EducationUserContext $context): array
    {
        $lessonStudents = $this->lessonRepository->listLessonStudents((int) $lesson->id, (int) $context->tenantId);
        $attendanceRows = $this->lessonRepository->listAttendanceRows((int) $lesson->id, (int) $context->tenantId);
        $leaveRows = $this->lessonRepository->listApprovedLeaveRows((int) $lesson->id, (int) $context->tenantId);

        return array_map(static function (array $student) use ($attendanceRows, $leaveRows): array {
            $attendance = $attendanceRows[$student['id']] ?? null;
            $leave = $leaveRows[$student['id']] ?? null;
            $hasLeave = \is_array($leave);

            return [
                'lesson_student_id' => (int) $student['id'],
                'student_id' => (int) $student['student_id'],
                'student_name_snapshot' => (string) $student['student_name_snapshot'],
                'student_no_snapshot' => (string) $student['student_no_snapshot'],
                'account_id' => (int) $student['account_id'],
                'default_attendance_status' => $hasLeave ? 'leave' : 'present',
                'default_consume_policy' => $hasLeave ? 'no_consume' : 'consume',
                'default_consumed_units' => $hasLeave ? '0.00' : (string) $student['lesson_units'],
                'existing_attendance_status' => \is_array($attendance) ? $attendance['attendance_status'] : null,
                'existing_consumed_units' => \is_array($attendance) ? (string) $attendance['consumed_units'] : null,
                'leave_request_id' => $hasLeave ? (int) $leave['id'] : null,
                'leave_status' => $hasLeave ? (string) $leave['status'] : null,
                'remark' => \is_array($attendance) ? $attendance['remark'] : null,
            ];
        }, $lessonStudents);
    }

    private function normalizeSubmittedRecords(array $records, array $lessonStudents): array
    {
        $plannedIds = array_map(static fn (array $row): int => (int) $row['id'], $lessonStudents);
        sort($plannedIds);

        $normalized = [];
        foreach ($records as $record) {
            $lessonStudentId = (int) ($record['lesson_student_id'] ?? 0);
            $normalized[$lessonStudentId] = [
                'lesson_student_id' => $lessonStudentId,
                'attendance_status' => (string) ($record['attendance_status'] ?? ''),
                'consume_policy' => (string) ($record['consume_policy'] ?? ''),
                'consumed_units' => $this->decimal($record['consumed_units'] ?? '0'),
                'remark' => $record['remark'] ?? null,
            ];
        }
        ksort($normalized);
        $submittedIds = array_keys($normalized);
        sort($submittedIds);
        if ($plannedIds !== $submittedIds) {
            throw new BusinessException(ResultCode::CONFLICT, 'attendance records must match lesson students', ['lesson_student_ids' => $plannedIds]);
        }

        return $normalized;
    }

    private function assertSameExistingPayload(int $lessonId, array $normalized, array $existing): void
    {
        foreach ($existing as $lessonStudentId => $attendance) {
            $record = $normalized[(int) $lessonStudentId] ?? null;
            if ($record === null
                || $record['attendance_status'] !== $attendance['attendance_status']
                || $record['consume_policy'] !== $attendance['consume_policy']
                || $record['consumed_units'] !== $this->decimal($attendance['consumed_units'])) {
                throw new BusinessException(ResultCode::CONFLICT, 'attendance already submitted with different payload', ['lesson_id' => $lessonId]);
            }
        }
    }

    private function attendanceRowsWithStudents(int $lessonId, int $tenantId): array
    {
        $students = $this->lessonRepository->listLessonStudents($lessonId, $tenantId);
        $studentNames = [];
        foreach ($students as $student) {
            $studentNames[(int) $student['id']] = $student['student_name_snapshot'];
        }

        return array_map(static function (array $attendance) use ($studentNames): array {
            $attendance['student_name_snapshot'] = $studentNames[(int) $attendance['lesson_student_id']] ?? null;

            return $attendance;
        }, array_values($this->lessonRepository->listAttendanceRows($lessonId, $tenantId)));
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function dispatchAudit(EducationLesson $lesson, EducationUserContext $context, array $summary): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'teacher_mobile_attendance',
            action: 'education.academic.teacher_mobile.attendance_submitted',
            businessType: 'lesson',
            businessId: (int) $lesson->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $summary,
            metadata: ['tenant_id' => (int) $lesson->tenant_id, 'campus_id' => (int) $lesson->campus_id],
            summary: 'Teacher mobile attendance submitted'
        ));
    }
}

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
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\Education\Academic\AttendanceRepository;
use App\Repository\Education\Academic\ConsumptionRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class AttendanceService
{
    public function __construct(
        private readonly AttendanceRepository $repository,
        private readonly ConsumptionRepository $consumptionRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function lessonPage(array $filters, EducationUserContext $context): array
    {
        return $this->repository->lessonPage($filters, $context);
    }

    public function lessonDetail(int $lessonId, EducationUserContext $context): array
    {
        return $this->repository->lessonDetail($lessonId, $context);
    }

    public function submit(int $lessonId, array $records, ?string $submittedAt, EducationUserContext $context, ?int $operatorId): array
    {
        $lesson = $this->findLesson($lessonId, $context);
        if ($lesson->status === 'cancelled') {
            throw new BusinessException(ResultCode::CONFLICT, 'cancelled lesson cannot submit attendance', ['lesson_id' => $lessonId]);
        }
        $lessonStudents = EducationLessonStudent::query()
            ->where('tenant_id', $lesson->tenant_id)
            ->where('lesson_id', $lessonId)
            ->where('status', '<>', 'cancelled')
            ->orderBy('id')
            ->get()
            ->all();
        $normalized = $this->normalizeRecords($records, $lessonStudents);
        $existing = $this->assertIdempotentOrReject($lessonId, $normalized, $context);
        if ($existing !== null) {
            return $this->summary($existing, []);
        }
        if ($lesson->status === 'completed') {
            throw new BusinessException(ResultCode::CONFLICT, 'completed lesson already has attendance', ['lesson_id' => $lessonId]);
        }

        return Db::transaction(function () use ($lesson, $lessonStudents, $normalized, $submittedAt, $context, $operatorId): array {
            $batchNo = 'ATT' . date('YmdHis') . str_pad((string) $lesson->tenant_id, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
            $attendanceRows = [];
            $consumptionRows = [];
            $studentById = [];
            foreach ($lessonStudents as $lessonStudent) {
                $studentById[(int) $lessonStudent->id] = $lessonStudent;
            }
            foreach ($normalized as $record) {
                /** @var EducationLessonStudent $lessonStudent */
                $lessonStudent = $studentById[$record['lesson_student_id']];
                $account = EducationStudentCourseAccount::query()->whereKey($lessonStudent->account_id)->lockForUpdate()->first();
                if (! $account instanceof EducationStudentCourseAccount) {
                    throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'student course account not found', ['account_id' => (int) $lessonStudent->account_id]);
                }
                if ($account->status !== 'active') {
                    throw new BusinessException(ResultCode::CONFLICT, 'student course account is not active', ['account_id' => (int) $account->id, 'status' => $account->status]);
                }
                if ((float) $account->available_units < (float) $record['consumed_units']) {
                    throw new BusinessException(ResultCode::CONFLICT, 'student course account has insufficient available_units', ['account_id' => (int) $account->id, 'available_units' => $account->available_units]);
                }
                $attendanceRows[] = [
                    'tenant_id' => (int) $lesson->tenant_id,
                    'campus_id' => (int) $lesson->campus_id,
                    'lesson_id' => (int) $lesson->id,
                    'lesson_student_id' => (int) $lessonStudent->id,
                    'class_id' => (int) $lessonStudent->class_id,
                    'course_id' => (int) $lessonStudent->course_id,
                    'student_id' => (int) $lessonStudent->student_id,
                    'account_id' => (int) $lessonStudent->account_id,
                    'attendance_status' => $record['attendance_status'],
                    'consume_policy' => $record['consume_policy'],
                    'planned_units' => $this->decimal($lessonStudent->lesson_units),
                    'consumed_units' => $record['consumed_units'],
                    'consumption_status' => $record['consume_policy'] === 'consume' ? 'active' : 'none',
                    'submitted_at' => $submittedAt ?? Carbon::now()->toDateTimeString(),
                    'submitted_by' => $operatorId,
                    'attendance_batch_no' => $batchNo,
                    'remark' => $record['remark'] ?? null,
                    'created_by' => $operatorId,
                    'updated_by' => $operatorId,
                ];
            }
            $attendances = $this->repository->bulkCreate($attendanceRows);
            foreach ($attendances as $attendance) {
                if ($attendance['consume_policy'] !== 'consume') {
                    continue;
                }
                $account = EducationStudentCourseAccount::query()->whereKey($attendance['account_id'])->lockForUpdate()->first();
                if (! $account instanceof EducationStudentCourseAccount) {
                    throw new \RuntimeException('Student course account not found.');
                }
                $beforeAvailable = $this->decimal($account->available_units);
                $beforeConsumed = $this->decimal($account->consumed_units);
                $afterAvailable = $this->subtract($beforeAvailable, $attendance['consumed_units']);
                $afterConsumed = $this->add($beforeConsumed, $attendance['consumed_units']);
                $account->available_units = $afterAvailable;
                $account->consumed_units = $afterConsumed;
                $account->updated_by = $operatorId;
                $account->save();
                $consumptionRows[] = $this->consumptionRepository->createConsumption([
                    'tenant_id' => $attendance['tenant_id'],
                    'campus_id' => $attendance['campus_id'],
                    'consumption_no' => $this->consumptionRepository->nextConsumptionNo($attendance['tenant_id'], $attendance['campus_id']),
                    'account_id' => $attendance['account_id'],
                    'student_id' => $attendance['student_id'],
                    'course_id' => $attendance['course_id'],
                    'lesson_id' => $attendance['lesson_id'],
                    'lesson_student_id' => $attendance['lesson_student_id'],
                    'attendance_id' => $attendance['id'],
                    'source_type' => 'attendance',
                    'direction' => 'decrease',
                    'units' => $attendance['consumed_units'],
                    'before_available_units' => $beforeAvailable,
                    'after_available_units' => $afterAvailable,
                    'before_consumed_units' => $beforeConsumed,
                    'after_consumed_units' => $afterConsumed,
                    'status' => 'active',
                    'reason' => 'attendance',
                    'created_by' => $operatorId,
                    'updated_by' => $operatorId,
                ])->toArray();
            }
            $lesson->status = 'completed';
            $lesson->updated_by = $operatorId;
            $lesson->save();
            $this->dispatchAudit($lesson, $context, $attendances);

            return $this->summary($attendances, $consumptionRows);
        });
    }

    private function normalizeRecords(array $records, array $lessonStudents): array
    {
        if ($lessonStudents === []) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'lesson has no active lesson students');
        }
        $planned = [];
        foreach ($lessonStudents as $lessonStudent) {
            $planned[(int) $lessonStudent->id] = $this->decimal($lessonStudent->lesson_units);
        }
        $normalized = [];
        foreach ($records as $record) {
            $lessonStudentId = (int) ($record['lesson_student_id'] ?? 0);
            if (! isset($planned[$lessonStudentId])) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'attendance records must match lesson students');
            }
            $status = (string) ($record['attendance_status'] ?? '');
            $policy = (string) ($record['consume_policy'] ?? '');
            $units = $this->decimal($record['consumed_units'] ?? '0');
            if ($status === 'leave' && $policy === 'consume') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'leave attendance cannot consume units');
            }
            if ($policy === 'no_consume' && $units !== '0.00') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'no_consume records must use zero units');
            }
            if ($policy === 'consume' && (float) $units <= 0) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'consume records must use positive units');
            }
            if (! \in_array($status, ['present', 'late', 'absent', 'leave'], true) || ! \in_array($policy, ['consume', 'no_consume'], true)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid attendance record');
            }
            $normalized[$lessonStudentId] = [
                'lesson_student_id' => $lessonStudentId,
                'attendance_status' => $status,
                'consume_policy' => $policy,
                'consumed_units' => $units,
                'remark' => $record['remark'] ?? null,
            ];
        }
        ksort($normalized);
        if (array_keys($planned) !== array_keys($normalized)) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'attendance records must cover all lesson students');
        }

        return array_values($normalized);
    }

    private function assertIdempotentOrReject(int $lessonId, array $normalizedRecords, EducationUserContext $context): ?array
    {
        $existing = $this->repository->existingByLesson($lessonId, (int) $context->tenantId);
        if ($existing === []) {
            return null;
        }
        $expected = [];
        foreach ($normalizedRecords as $record) {
            $expected[(int) $record['lesson_student_id']] = $record;
        }
        foreach ($existing as $attendance) {
            $record = $expected[(int) $attendance['lesson_student_id']] ?? null;
            if ($record === null
                || $record['attendance_status'] !== $attendance['attendance_status']
                || $record['consume_policy'] !== $attendance['consume_policy']
                || $record['consumed_units'] !== $this->decimal($attendance['consumed_units'])) {
                throw new BusinessException(ResultCode::CONFLICT, 'attendance was already submitted with different records', ['lesson_id' => $lessonId]);
            }
        }

        return $existing;
    }

    private function findLesson(int $lessonId, EducationUserContext $context): EducationLesson
    {
        $detail = $this->repository->lessonDetail($lessonId, $context);
        if (! \is_array($detail['lesson'])) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found', ['lesson_id' => $lessonId]);
        }
        $lesson = EducationLesson::query()->whereKey($lessonId)->first();
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found', ['lesson_id' => $lessonId]);
        }

        return $lesson;
    }

    private function summary(array $attendances, array $consumptions): array
    {
        $total = '0.00';
        foreach ($attendances as $attendance) {
            $total = $this->add($total, $attendance['consumed_units']);
        }

        return [
            'attendance_count' => \count($attendances),
            'consumed_count' => \count($consumptions) > 0 ? \count($consumptions) : \count(array_filter($attendances, static fn (array $row): bool => $row['consume_policy'] === 'consume')),
            'total_consumed_units' => $total,
            'attendances' => $attendances,
            'consumptions' => $consumptions,
        ];
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function add(string $left, string $right): string
    {
        return number_format((float) $left + (float) $right, 2, '.', '');
    }

    private function subtract(string $left, string $right): string
    {
        return number_format((float) $left - (float) $right, 2, '.', '');
    }

    private function dispatchAudit(EducationLesson $lesson, EducationUserContext $context, array $attendances): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'attendance',
            action: 'education.academic.attendance.submitted',
            businessType: 'attendance',
            businessId: (int) $lesson->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['attendances' => $attendances],
            metadata: ['tenant_id' => (int) $lesson->tenant_id, 'campus_id' => (int) $lesson->campus_id],
            summary: 'Attendance submitted'
        ));
    }
}

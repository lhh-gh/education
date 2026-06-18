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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Repository\Education\Academic\LessonRepository;
use App\Repository\Education\Academic\LessonStudentRepository;

final class SchedulingConflictService
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
        private readonly LessonStudentRepository $lessonStudentRepository
    ) {}

    public function checkSingle(array $payload): array
    {
        $conflicts = [];
        foreach (['teacher_id' => 'teacher', 'classroom_id' => 'classroom', 'class_id' => 'class'] as $field => $type) {
            if (! \array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                continue;
            }
            $rows = $this->lessonRepository->overlappingLessons([
                'tenant_id' => (int) $payload['tenant_id'],
                'campus_id' => (int) $payload['campus_id'],
                $field => (int) $payload[$field],
                'start_at' => (string) $payload['start_at'],
                'end_at' => (string) $payload['end_at'],
            ], isset($payload['exclude_lesson_id']) ? (int) $payload['exclude_lesson_id'] : null);
            if ($rows !== []) {
                $conflicts[] = $this->conflict($type, $rows, (string) $payload['start_at'], (string) $payload['end_at']);
            }
        }

        $studentIds = array_values(array_unique(array_map('intval', $payload['student_ids'] ?? [])));
        $studentRows = $this->lessonStudentRepository->overlappingStudentLessons(
            $studentIds,
            (string) $payload['start_at'],
            (string) $payload['end_at'],
            (int) $payload['tenant_id'],
            (int) $payload['campus_id'],
            isset($payload['exclude_lesson_id']) ? (int) $payload['exclude_lesson_id'] : null
        );
        if ($studentRows !== []) {
            $conflicts[] = $this->conflict('student', $studentRows, (string) $payload['start_at'], (string) $payload['end_at']);
        }

        return ['has_conflict' => $conflicts !== [], 'conflicts' => $conflicts];
    }

    public function assertNoConflict(array $payload): void
    {
        $result = $this->checkSingle($payload);
        if ($result['has_conflict']) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson schedule conflict', $result);
        }
    }

    public function checkBatch(array $payloads): array
    {
        $conflicts = [];
        foreach ($payloads as $index => $payload) {
            $result = $this->checkSingle($payload);
            foreach ($result['conflicts'] as $conflict) {
                $conflicts[] = $conflict;
            }
            foreach (\array_slice($payloads, $index + 1) as $other) {
                if (! $this->overlaps((string) $payload['start_at'], (string) $payload['end_at'], (string) $other['start_at'], (string) $other['end_at'])) {
                    continue;
                }
                foreach (['teacher_id' => 'teacher', 'classroom_id' => 'classroom', 'class_id' => 'class'] as $field => $type) {
                    if (($payload[$field] ?? null) !== null && (int) $payload[$field] === (int) ($other[$field] ?? 0)) {
                        $conflicts[] = [
                            'conflict_type' => $type,
                            'message' => $type . ' conflict inside batch',
                            'lesson_ids' => [],
                            'start_at' => (string) $payload['start_at'],
                            'end_at' => (string) $payload['end_at'],
                        ];
                    }
                }
                $sharedStudents = array_intersect(array_map('intval', $payload['student_ids'] ?? []), array_map('intval', $other['student_ids'] ?? []));
                if ($sharedStudents !== []) {
                    $conflicts[] = [
                        'conflict_type' => 'student',
                        'message' => 'student conflict inside batch',
                        'lesson_ids' => [],
                        'start_at' => (string) $payload['start_at'],
                        'end_at' => (string) $payload['end_at'],
                    ];
                }
            }
        }

        return ['has_conflict' => $conflicts !== [], 'conflicts' => $conflicts];
    }

    private function conflict(string $type, array $rows, string $startAt, string $endAt): array
    {
        $lessonIds = array_values(array_unique(array_map(static fn (array $row): int => (int) ($row['lesson_id'] ?? $row['id']), $rows)));

        return [
            'conflict_type' => $type,
            'message' => $type . ' schedule conflict',
            'lesson_ids' => $lessonIds,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ];
    }

    private function overlaps(string $leftStart, string $leftEnd, string $rightStart, string $rightEnd): bool
    {
        return $leftStart < $rightEnd && $leftEnd > $rightStart;
    }
}

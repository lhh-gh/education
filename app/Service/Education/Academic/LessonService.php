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
use App\Repository\Education\Academic\LessonRepository;
use App\Repository\Education\Academic\LessonStudentRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class LessonService
{
    public function __construct(
        private readonly LessonRepository $repository,
        private readonly LessonStudentRepository $lessonStudentRepository,
        private readonly SchedulingConflictService $conflictService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function detail(int $id, EducationUserContext $context): array
    {
        $lesson = $this->findScoped($id, $context);

        return [
            'lesson' => $lesson->toArray(),
            'students' => $this->lessonStudentRepository->listByLesson((int) $lesson->id, $context),
        ];
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationLesson
    {
        $lesson = $this->findScoped($id, $context);
        $this->assertScheduled($lesson);
        $payload = [];
        foreach (['title', 'teacher_id', 'classroom_id', 'start_at', 'end_at', 'lesson_units', 'remark'] as $field) {
            if (\array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }
        $startAt = (string) ($payload['start_at'] ?? $lesson->start_at?->toDateTimeString());
        $endAt = (string) ($payload['end_at'] ?? $lesson->end_at?->toDateTimeString());
        $this->assertTime($startAt, $endAt);
        $payload['start_at'] = Carbon::parse($startAt)->toDateTimeString();
        $payload['end_at'] = Carbon::parse($endAt)->toDateTimeString();
        $payload['duration_minutes'] = Carbon::parse($startAt)->diffInMinutes(Carbon::parse($endAt));
        $payload['teacher_id'] = (int) ($payload['teacher_id'] ?? $lesson->teacher_id);
        $payload['classroom_id'] = \array_key_exists('classroom_id', $payload) ? ($payload['classroom_id'] === null || $payload['classroom_id'] === '' ? null : (int) $payload['classroom_id']) : $lesson->classroom_id;
        if (isset($payload['lesson_units'])) {
            $payload['lesson_units'] = $this->decimal($payload['lesson_units']);
        }

        $studentIds = EducationLessonStudent::query()
            ->where('lesson_id', $lesson->id)
            ->where('status', 'planned')
            ->pluck('student_id')
            ->map(static fn ($id): int => (int) $id)
            ->toArray();
        $this->conflictService->assertNoConflict([
            'tenant_id' => (int) $lesson->tenant_id,
            'campus_id' => (int) $lesson->campus_id,
            'class_id' => (int) $lesson->class_id,
            'teacher_id' => (int) $payload['teacher_id'],
            'classroom_id' => $payload['classroom_id'],
            'student_ids' => $studentIds,
            'start_at' => $payload['start_at'],
            'end_at' => $payload['end_at'],
            'exclude_lesson_id' => (int) $lesson->id,
        ]);

        return Db::transaction(function () use ($lesson, $payload, $operatorId, $context): EducationLesson {
            $before = $lesson->toArray();
            $updated = $this->repository->updateScheduled((int) $lesson->id, $payload, $operatorId);
            if (isset($payload['lesson_units'])) {
                EducationLessonStudent::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('status', 'planned')
                    ->update([
                        'lesson_units' => $payload['lesson_units'],
                        'updated_by' => $operatorId,
                    ]);
            }
            $this->dispatchAudit('updated', $updated, $context, $before, $updated->toArray());

            return $updated;
        });
    }

    public function cancel(int $id, string $reason, EducationUserContext $context, ?int $operatorId): EducationLesson
    {
        $lesson = $this->findScoped($id, $context);
        $this->assertScheduled($lesson);

        return Db::transaction(function () use ($lesson, $reason, $operatorId, $context): EducationLesson {
            $before = $lesson->toArray();
            $cancelled = $this->repository->cancel((int) $lesson->id, $reason, $operatorId);
            $this->lessonStudentRepository->cancelByLesson((int) $lesson->id, $operatorId);
            $this->dispatchAudit('cancelled', $cancelled, $context, $before, $cancelled->toArray());

            return $cancelled;
        });
    }

    public function delete(int $id, EducationUserContext $context, ?int $operatorId): bool
    {
        $lesson = $this->findScoped($id, $context);
        $this->assertScheduled($lesson);
        if ($this->repository->hasAttendanceOrConsumptionReferences((int) $lesson->id, (int) $lesson->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson is referenced by attendance or consumption', ['id' => (int) $lesson->id]);
        }
        $lesson->updated_by = $operatorId;
        $lesson->save();

        return (bool) $lesson->delete();
    }

    private function findScoped(int $id, EducationUserContext $context): EducationLesson
    {
        $lesson = $this->repository->findScoped($id, $context);
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found', ['id' => $id]);
        }

        return $lesson;
    }

    private function assertScheduled(EducationLesson $lesson): void
    {
        if ($lesson->status !== 'scheduled') {
            throw new BusinessException(ResultCode::CONFLICT, 'only scheduled lessons can be changed', ['id' => (int) $lesson->id, 'status' => $lesson->status]);
        }
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

    private function extractPage(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationLesson $lesson, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'lesson',
            action: 'education.academic.lesson.' . $action,
            businessType: 'lesson',
            businessId: (int) $lesson->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $lesson->tenant_id, 'campus_id' => (int) $lesson->campus_id],
            summary: 'Lesson ' . $action
        ));
    }
}

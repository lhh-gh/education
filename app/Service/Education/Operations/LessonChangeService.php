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

namespace App\Service\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Repository\Education\Operations\LessonChangeRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class LessonChangeService
{
    public function __construct(private readonly LessonChangeRepository $repository) {}

    public function createRequest(array $data, EducationUserContext $context): array
    {
        $lesson = $this->lesson((int) $data['lesson_id'], $context);
        if ($this->repository->findPendingByLesson((int) $lesson->tenant_id, (int) $lesson->id) instanceof EducationLessonChangeRequest) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson already has pending change request', ['lesson_id' => (int) $lesson->id]);
        }
        if (EducationLessonAttendance::query()->where('tenant_id', $lesson->tenant_id)->where('lesson_id', $lesson->id)->exists()
            || EducationLessonConsumption::query()->where('tenant_id', $lesson->tenant_id)->where('lesson_id', $lesson->id)->exists()) {
            throw new BusinessException(ResultCode::CONFLICT, 'lesson has been consumed and cannot be changed', ['lesson_id' => (int) $lesson->id]);
        }

        $request = $this->repository->createRequest([
            'tenant_id' => (int) $lesson->tenant_id,
            'campus_id' => (int) $lesson->campus_id,
            'lesson_id' => (int) $lesson->id,
            'change_type' => (string) $data['change_type'],
            'status' => 'pending',
            'old_values_json' => $lesson->toArray(),
            'new_values_json' => $data['new_values_json'],
            'reason' => trim((string) $data['reason']),
            'requested_by' => $context->userId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $request->toArray();
    }

    public function approve(int $id, array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($id, $context): array {
            $request = $this->changeRequest($id, $context);
            if ($request->status !== 'pending') {
                throw new BusinessException(ResultCode::CONFLICT, 'lesson change request is not pending', ['id' => $id, 'status' => $request->status]);
            }
            $lesson = $this->lesson((int) $request->lesson_id, $context);
            $this->assertNoTimeConflict($lesson, (array) $request->new_values_json);
            $request->update([
                'status' => 'approved',
                'approved_by' => $context->userId,
                'approved_at' => Carbon::now()->toDateTimeString(),
                'updated_by' => $context->userId,
            ]);

            return $request->refresh()->toArray();
        });
    }

    public function reject(int $id, EducationUserContext $context): array
    {
        $request = $this->changeRequest($id, $context);
        $request->update(['status' => 'rejected', 'approved_by' => $context->userId, 'approved_at' => Carbon::now()->toDateTimeString(), 'updated_by' => $context->userId]);

        return $request->refresh()->toArray();
    }

    public function apply(int $id, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($id, $context): array {
            $request = $this->changeRequest($id, $context);
            if ($request->status !== 'approved') {
                throw new BusinessException(ResultCode::CONFLICT, 'lesson change request is not approved', ['id' => $id, 'status' => $request->status]);
            }
            $lesson = $this->lesson((int) $request->lesson_id, $context);
            $before = $lesson->toArray();
            $changes = $this->lessonUpdatePayload($lesson, (array) $request->new_values_json);
            $lesson->update($changes + ['updated_by' => $context->userId]);
            $request->update(['status' => 'applied', 'applied_at' => Carbon::now()->toDateTimeString(), 'updated_by' => $context->userId]);
            $this->repository->writeLog([
                'tenant_id' => (int) $lesson->tenant_id,
                'campus_id' => (int) $lesson->campus_id,
                'lesson_id' => (int) $lesson->id,
                'change_request_id' => (int) $request->id,
                'change_type' => (string) $request->change_type,
                'before_json' => $before,
                'after_json' => $lesson->refresh()->toArray(),
                'operator_id' => $context->userId,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return $request->refresh()->toArray();
        });
    }

    public function batchChange(array $lessonIds, string $changeType, string $reason, EducationUserContext $context): array
    {
        $success = [];
        $failed = [];
        foreach ($lessonIds as $lessonId) {
            try {
                $request = $this->createRequest([
                    'lesson_id' => (int) $lessonId,
                    'change_type' => $changeType,
                    'new_values_json' => [],
                    'reason' => $reason,
                ], $context);
                $success[] = (int) $request['lesson_id'];
            } catch (BusinessException $exception) {
                $failed[] = ['lesson_id' => (int) $lessonId, 'message' => $exception->getMessage()];
            }
        }

        return ['success_ids' => $success, 'failed' => $failed];
    }

    private function changeRequest(int $id, EducationUserContext $context): EducationLessonChangeRequest
    {
        $request = $this->repository->lockById($context->tenantId, $id);
        if (! $request instanceof EducationLessonChangeRequest) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson change request not found', ['id' => $id]);
        }
        if (! $context->canAccessCampus((int) $request->campus_id) && $context->campusIds !== []) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'lesson is outside current campus scope', ['lesson_id' => (int) $request->lesson_id]);
        }

        return $request;
    }

    private function lesson(int $id, EducationUserContext $context): EducationLesson
    {
        $query = EducationLesson::query()->where('tenant_id', $context->tenantId)->whereKey($id);
        if ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
        $lesson = $query->first();
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found in current context', ['lesson_id' => $id]);
        }

        return $lesson;
    }

    private function assertNoTimeConflict(EducationLesson $lesson, array $newValues): void
    {
        if (! isset($newValues['start_time'], $newValues['end_time']) && ! isset($newValues['start_at'], $newValues['end_at'])) {
            return;
        }
        $startAt = Carbon::parse((string) ($newValues['start_time'] ?? $newValues['start_at']))->toDateTimeString();
        $endAt = Carbon::parse((string) ($newValues['end_time'] ?? $newValues['end_at']))->toDateTimeString();
        $teacherId = (int) ($newValues['teacher_id'] ?? $lesson->teacher_id);
        $classroomId = isset($newValues['classroom_id']) ? (int) $newValues['classroom_id'] : $lesson->classroom_id;
        $conflict = EducationLesson::query()
            ->where('tenant_id', $lesson->tenant_id)
            ->where('id', '<>', $lesson->id)
            ->where('status', 'scheduled')
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->where(static function ($query) use ($teacherId, $classroomId): void {
                $query->where('teacher_id', $teacherId);
                if ($classroomId !== null) {
                    $query->orWhere('classroom_id', $classroomId);
                }
            })
            ->first();
        if ($conflict instanceof EducationLesson) {
            if ((int) $conflict->teacher_id === $teacherId) {
                throw new BusinessException(ResultCode::CONFLICT, 'teacher time conflict', ['teacher_id' => $teacherId, 'conflict_lesson_id' => (int) $conflict->id]);
            }
            throw new BusinessException(ResultCode::CONFLICT, 'classroom time conflict', ['classroom_id' => $classroomId, 'conflict_lesson_id' => (int) $conflict->id]);
        }
    }

    private function lessonUpdatePayload(EducationLesson $lesson, array $newValues): array
    {
        $payload = [];
        foreach (['teacher_id', 'classroom_id', 'title', 'status'] as $field) {
            if (\array_key_exists($field, $newValues)) {
                $payload[$field] = $newValues[$field];
            }
        }
        if (isset($newValues['start_time']) || isset($newValues['start_at'])) {
            $payload['start_at'] = Carbon::parse((string) ($newValues['start_time'] ?? $newValues['start_at']))->toDateTimeString();
        }
        if (isset($newValues['end_time']) || isset($newValues['end_at'])) {
            $payload['end_at'] = Carbon::parse((string) ($newValues['end_time'] ?? $newValues['end_at']))->toDateTimeString();
        }
        if (isset($payload['start_at'], $payload['end_at'])) {
            $payload['duration_minutes'] = Carbon::parse($payload['start_at'])->diffInMinutes(Carbon::parse($payload['end_at']));
        }
        if ($payload === []) {
            $payload['status'] = $newValues['status'] ?? $lesson->status;
        }

        return $payload;
    }
}

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
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Operations\EducationLessonConsumptionReview;
use App\Repository\Education\Operations\ConsumptionAdjustmentRepository;
use App\Repository\Education\Operations\ConsumptionReviewRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class ConsumptionReviewService
{
    public function __construct(
        private readonly ConsumptionReviewRepository $reviewRepository,
        private readonly ConsumptionAdjustmentRepository $adjustmentRepository
    ) {}

    public function createFromAttendance(int $lessonId, EducationUserContext $context): array
    {
        $lesson = EducationLesson::query()->where('tenant_id', $context->tenantId)->whereKey($lessonId)->first();
        if (! $lesson instanceof EducationLesson) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson not found in current context', ['lesson_id' => $lessonId]);
        }
        $review = $this->reviewRepository->createFromAttendance([
            'tenant_id' => (int) $lesson->tenant_id,
            'campus_id' => (int) $lesson->campus_id,
            'lesson_id' => (int) $lesson->id,
            'status' => 'pending',
            'submitted_by' => $context->userId,
            'submitted_at' => Carbon::now()->toDateTimeString(),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $review->toArray();
    }

    public function approve(int $reviewId, EducationUserContext $context, ?string $note = null): array
    {
        $review = $this->review($reviewId, $context);
        if ($review->status !== 'pending') {
            throw new BusinessException(ResultCode::CONFLICT, 'consumption review is not pending', ['id' => $reviewId, 'status' => $review->status]);
        }

        return $this->reviewRepository->markApproved($review, $context->userId, $note)->toArray();
    }

    public function reject(int $reviewId, EducationUserContext $context, ?string $note = null): array
    {
        return $this->reviewRepository->markRejected($this->review($reviewId, $context), $context->userId, $note)->toArray();
    }

    public function createAdjustment(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $original = EducationLessonConsumption::query()
                ->where('tenant_id', $context->tenantId)
                ->whereKey((int) $data['original_consumption_id'])
                ->lockForUpdate()
                ->first();
            if (! $original instanceof EducationLessonConsumption) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'consumption not found', ['original_consumption_id' => (int) $data['original_consumption_id']]);
            }
            if ($original->status === 'reversed') {
                throw new BusinessException(ResultCode::CONFLICT, 'consumption has already been fully reversed', ['original_consumption_id' => (int) $original->id]);
            }
            $credits = number_format((float) $data['credits'], 2, '.', '');
            $units = number_format(abs((float) $credits), 2, '.', '');
            $account = EducationStudentCourseAccount::query()->where('tenant_id', $context->tenantId)->whereKey((int) $original->account_id)->lockForUpdate()->first();
            $beforeAvailable = (float) ($account?->available_units ?? $original->after_available_units);
            $beforeConsumed = (float) ($account?->consumed_units ?? $original->after_consumed_units);
            $afterAvailable = $beforeAvailable + (float) $units;
            $afterConsumed = max(0, $beforeConsumed - (float) $units);
            $rollback = EducationLessonConsumption::query()->create([
                'tenant_id' => (int) $original->tenant_id,
                'campus_id' => (int) $original->campus_id,
                'consumption_no' => 'REV' . date('YmdHis') . (int) $original->id,
                'account_id' => (int) $original->account_id,
                'student_id' => (int) $original->student_id,
                'course_id' => (int) $original->course_id,
                'lesson_id' => (int) $original->lesson_id,
                'lesson_student_id' => (int) $original->lesson_student_id,
                'attendance_id' => (int) $original->attendance_id,
                'source_type' => 'rollback',
                'direction' => 'increase',
                'units' => $units,
                'before_available_units' => number_format($beforeAvailable, 2, '.', ''),
                'after_available_units' => number_format($afterAvailable, 2, '.', ''),
                'before_consumed_units' => number_format($beforeConsumed, 2, '.', ''),
                'after_consumed_units' => number_format($afterConsumed, 2, '.', ''),
                'status' => 'active',
                'original_consumption_id' => (int) $original->id,
                'reason' => (string) $data['reason'],
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            if ($account instanceof EducationStudentCourseAccount) {
                $account->update([
                    'available_units' => number_format($afterAvailable, 2, '.', ''),
                    'consumed_units' => number_format($afterConsumed, 2, '.', ''),
                    'updated_by' => $context->userId,
                ]);
            }
            $original->update(['status' => 'reversed', 'reversed_at' => Carbon::now()->toDateTimeString(), 'reversed_by' => $context->userId]);
            $adjustment = $this->adjustmentRepository->createReverseRecord([
                'tenant_id' => (int) $original->tenant_id,
                'campus_id' => (int) $original->campus_id,
                'original_consumption_id' => (int) $original->id,
                'adjustment_consumption_id' => (int) $rollback->id,
                'student_id' => (int) $original->student_id,
                'student_course_account_id' => (int) $original->account_id,
                'credits' => $credits,
                'reason' => (string) $data['reason'],
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['adjustment_id' => (int) $adjustment->id, 'adjustment_consumption_id' => (int) $rollback->id];
        });
    }

    private function review(int $id, EducationUserContext $context): EducationLessonConsumptionReview
    {
        $review = $this->reviewRepository->lockReview($context->tenantId, $id);
        if (! $review instanceof EducationLessonConsumptionReview) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'consumption review not found', ['id' => $id]);
        }

        return $review;
    }
}

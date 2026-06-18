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

namespace App\Service\Education\Ai;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Repository\Education\Ai\AiGenerationRepository;
use App\Repository\Education\Ai\AiReviewRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class AiReviewService
{
    public function __construct(
        private readonly AiGenerationRepository $generationRepository,
        private readonly AiReviewRepository $reviewRepository
    ) {}

    /**
     * @return array{generation_result_id: int, review_status: string}
     */
    public function approve(int $resultId, EducationUserContext $context, string $note = ''): array
    {
        $result = $this->generationRepository->result($resultId);
        if ($result->safety_status instanceof \BackedEnum && $result->safety_status->value === 'blocked') {
            throw new BusinessException(ResultCode::CONFLICT, 'ai result was blocked by safety policy', ['generation_result_id' => $resultId]);
        }

        $result->review_status = 'approved';
        $result->visible_to_guardian = false;
        $result->updated_by = $context->userId;
        $result->save();
        $this->reviewRepository->create([
            'tenant_id' => (int) $result->tenant_id,
            'campus_id' => $result->campus_id,
            'generation_result_id' => $resultId,
            'reviewer_user_id' => $context->userId,
            'review_action' => 'approved',
            'review_note' => $note,
            'reviewed_at' => Carbon::now(),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['generation_result_id' => $resultId, 'review_status' => 'approved'];
    }
}

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

namespace App\Service\Education\Content;

use App\Model\Education\Content\EducationContentReviewRecord;
use App\Repository\Education\Content\ContentReviewRepository;

final class ContentReviewService
{
    public function __construct(private readonly ContentReviewRepository $reviews) {}

    /**
     * @param array<string, mixed> $data
     * @return array{content_review_id: int, status: string}
     */
    public function review(array $data): array
    {
        if (! \in_array($data['status'] ?? '', ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('review status must be approved or rejected', 422);
        }
        $review = $this->reviews->save($data + ['reviewed_at' => date('Y-m-d H:i:s')]);

        return ['content_review_id' => (int) $review->id, 'status' => (string) $data['status']];
    }

    /**
     * @return array{content_review_id: int, status: string}
     */
    public function reviewExisting(int $tenantId, int $id, int $reviewerId, string $status, ?string $note = null): array
    {
        if (! \in_array($status, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('review status must be approved or rejected', 422);
        }
        $review = EducationContentReviewRecord::query()->where('tenant_id', $tenantId)->findOrFail($id);
        $review->reviewer_id = $reviewerId;
        $review->status = $status;
        $review->review_note = $note;
        $review->reviewed_at = date('Y-m-d H:i:s');
        $review->save();

        return ['content_review_id' => (int) $review->id, 'status' => $status];
    }
}

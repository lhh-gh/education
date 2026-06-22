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

use App\Repository\Education\Content\ContentReviewRepository;
use App\Service\Education\Foundation\EducationUserContext;

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
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->reviews->page($filters, $context, $page, $pageSize);
    }

    /**
     * @return array{content_review_id: int, status: string}
     */
    public function reviewExisting(EducationUserContext $context, int $id, int $reviewerId, string $status, ?string $note = null): array
    {
        if (! \in_array($status, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('review status must be approved or rejected', 422);
        }
        $review = $this->reviews->findInContext($context, $id);
        $review->reviewer_id = $reviewerId;
        $review->status = $status;
        $review->review_note = $note;
        $review->reviewed_at = date('Y-m-d H:i:s');
        $review->save();

        return ['content_review_id' => (int) $review->id, 'status' => $status];
    }
}

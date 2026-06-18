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
}

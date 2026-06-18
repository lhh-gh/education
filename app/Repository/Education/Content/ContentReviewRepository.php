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

namespace App\Repository\Education\Content;

use App\Model\Education\Content\EducationContentReviewRecord;

final class ContentReviewRepository
{
    public function hasApprovedReview(int $tenantId, string $businessType, int $businessId): bool
    {
        return EducationContentReviewRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationContentReviewRecord
    {
        return EducationContentReviewRecord::query()->create($data);
    }
}

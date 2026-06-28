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
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class ContentReviewRepository
{
    public function hasApprovedReview(int $tenantId, ?int $campusId, string $businessType, int $businessId): bool
    {
        $query = EducationContentReviewRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->where('status', 'approved');
        if ($campusId === null) {
            $query->whereNull('campus_id');
        } else {
            $query->where('campus_id', $campusId);
        }

        return $query->exists();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationContentReviewRecord::query(), $filters, $context);
        foreach (['business_type', 'business_id', 'status'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }

    public function findInContext(EducationUserContext $context, int $id): EducationContentReviewRecord
    {
        return (new EducationScopeQuery())->applyTenantCampus(EducationContentReviewRecord::query(), [], $context)
            ->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationContentReviewRecord
    {
        return EducationContentReviewRecord::query()->create($data);
    }
}

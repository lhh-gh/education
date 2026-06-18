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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationLessonConsumptionReview;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

final class ConsumptionReviewRepository
{
    public function pagePending(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        foreach (['campus_id', 'status', 'submitted_by'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage((int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20))->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }

    public function lockReview(int $tenantId, int $id): ?EducationLessonConsumptionReview
    {
        return EducationLessonConsumptionReview::query()->where('tenant_id', $tenantId)->whereKey($id)->lockForUpdate()->first();
    }

    public function createFromAttendance(array $data): EducationLessonConsumptionReview
    {
        return EducationLessonConsumptionReview::query()->firstOrCreate([
            'tenant_id' => $data['tenant_id'],
            'lesson_id' => $data['lesson_id'],
        ], $data);
    }

    public function markApproved(EducationLessonConsumptionReview $review, ?int $reviewerId, ?string $note): EducationLessonConsumptionReview
    {
        $review->update(['status' => 'approved', 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'), 'review_note' => $note]);

        return $review->refresh();
    }

    public function markRejected(EducationLessonConsumptionReview $review, ?int $reviewerId, ?string $note): EducationLessonConsumptionReview
    {
        $review->update(['status' => 'rejected', 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'), 'review_note' => $note]);

        return $review->refresh();
    }

    private function scopedQuery(array $params, EducationUserContext $context): mixed
    {
        $query = EducationLessonConsumptionReview::query();
        if ($context->platformAccess) {
            if (isset($params['tenant_id']) && $params['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $params['tenant_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        if ($context->roleCode !== EducationRoleCode::TenantAdmin) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }

        return $query;
    }
}

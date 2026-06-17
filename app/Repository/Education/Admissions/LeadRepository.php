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

namespace App\Repository\Education\Admissions;

use App\Model\Education\Admissions\EducationLead;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class LeadRepository
{
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($filters, $context);
        foreach (['source_id', 'stage', 'status', 'owner_user_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['next_follow_start']) && $filters['next_follow_start'] !== '') {
            $query->where('next_follow_at', '>=', $filters['next_follow_start']);
        }
        if (isset($filters['next_follow_end']) && $filters['next_follow_end'] !== '') {
            $query->where('next_follow_at', '<=', $filters['next_follow_end']);
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static fn ($query) => $query->where('lead_no', 'like', $keyword)
                ->orWhere('contact_name', 'like', $keyword)
                ->orWhere('contact_mobile', 'like', $keyword));
        }

        return $this->paginate($query->orderByDesc('id'), $filters);
    }

    public function findByMobile(int $tenantId, string $mobile): ?EducationLead
    {
        $lead = EducationLead::query()
            ->where('tenant_id', $tenantId)
            ->where('contact_mobile', $mobile)
            ->whereIn('status', ['active', 'converted'])
            ->first();

        return $lead instanceof EducationLead ? $lead : null;
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationLead
    {
        $lead = $this->scopedQuery([], $context)->whereKey($id)->first();

        return $lead instanceof EducationLead ? $lead : null;
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationLead
    {
        $lead = $this->scopedQuery([], $context)->whereKey($id)->lockForUpdate()->first();

        return $lead instanceof EducationLead ? $lead : null;
    }

    public function create(array $data): EducationLead
    {
        return EducationLead::query()->create($data);
    }

    public function nextLeadNo(int $tenantId, ?int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) (($campusId ?? 0) % 1000), 3, '0', \STR_PAD_LEFT);

        return 'LD' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
    }

    private function scopedQuery(array $filters, EducationUserContext $context): mixed
    {
        $query = EducationLead::query()->where('tenant_id', $context->tenantId);
        if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
            $query->where('campus_id', (int) $filters['campus_id']);
        } elseif ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
        if ($context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->platformAccess && $context->userId > 0) {
            $query->where(static fn ($query) => $query->where('owner_user_id', $context->userId)->orWhereNull('owner_user_id'));
        }

        return $query;
    }

    private function paginate(mixed $query, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}

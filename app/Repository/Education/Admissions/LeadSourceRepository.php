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

use App\Model\Education\Admissions\EducationLeadSource;
use App\Service\Education\Foundation\EducationUserContext;

final class LeadSourceRepository
{
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = EducationLeadSource::query()->where('tenant_id', $context->tenantId);
        $this->applyCampus($query, $filters, $context);
        foreach (['status', 'channel_type'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(static fn ($query) => $query->where('code', 'like', $keyword)->orWhere('name', 'like', $keyword));
        }

        return $this->paginate($query->orderBy('sort_order')->orderByDesc('id'), $filters);
    }

    public function findByCode(int $tenantId, string $code, ?int $exceptId = null): ?EducationLeadSource
    {
        $query = EducationLeadSource::query()->where('tenant_id', $tenantId)->where('code', $code);
        if ($exceptId !== null) {
            $query->where('id', '<>', $exceptId);
        }
        $source = $query->first();

        return $source instanceof EducationLeadSource ? $source : null;
    }

    public function create(array $data): EducationLeadSource
    {
        return EducationLeadSource::query()->create($data);
    }

    private function applyCampus(mixed $query, array $filters, EducationUserContext $context): void
    {
        if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
            $query->where('campus_id', (int) $filters['campus_id']);
            return;
        }
        if ($context->campusIds !== []) {
            $query->whereIn('campus_id', $context->campusIds);
        }
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

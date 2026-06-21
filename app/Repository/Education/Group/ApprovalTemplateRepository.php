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

namespace App\Repository\Education\Group;

use App\Model\Education\Group\EducationApprovalNode;
use App\Model\Education\Group\EducationApprovalTemplate;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class ApprovalTemplateRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createTemplate(array $data): EducationApprovalTemplate
    {
        return EducationApprovalTemplate::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createNode(array $data): EducationApprovalNode
    {
        return EducationApprovalNode::query()->create($data);
    }

    public function findTemplate(int $id, EducationUserContext $context): ?EducationApprovalTemplate
    {
        $row = EducationApprovalTemplate::query()->where('tenant_id', $context->tenantId)->whereKey($id)->first();

        return $row instanceof EducationApprovalTemplate ? $row : null;
    }

    public function firstNode(int $templateId, EducationUserContext $context): ?EducationApprovalNode
    {
        $row = EducationApprovalNode::query()
            ->where('tenant_id', $context->tenantId)
            ->where('template_id', $templateId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $row instanceof EducationApprovalNode ? $row : null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampusColumns(
            EducationApprovalTemplate::query(),
            $filters,
            $context,
            campusScoped: false
        );
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationApprovalTemplate $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}

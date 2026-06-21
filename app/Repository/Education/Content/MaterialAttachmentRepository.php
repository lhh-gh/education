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

use App\Model\Education\Content\EducationLearningMaterialAttachment;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class MaterialAttachmentRepository
{
    /**
     * @param list<array<string, mixed>> $attachments
     */
    public function replaceForVersion(int $tenantId, ?int $campusId, int $versionId, array $attachments): void
    {
        EducationLearningMaterialAttachment::query()
            ->where('tenant_id', $tenantId)
            ->where('material_version_id', $versionId)
            ->delete();

        foreach ($attachments as $index => $attachment) {
            EducationLearningMaterialAttachment::query()->create($attachment + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'material_version_id' => $versionId,
                'file_size' => 0,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForVersion(int $tenantId, int $versionId): array
    {
        return EducationLearningMaterialAttachment::query()
            ->where('tenant_id', $tenantId)
            ->where('material_version_id', $versionId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampus(EducationLearningMaterialAttachment::query(), $filters, $context);
        if (($filters['material_version_id'] ?? '') !== '') {
            $query->where('material_version_id', (int) $filters['material_version_id']);
        }
        if (($filters['file_type'] ?? '') !== '') {
            $query->where('file_type', (string) $filters['file_type']);
        }

        $total = (int) (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}

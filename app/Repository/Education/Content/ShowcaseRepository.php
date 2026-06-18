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

use App\Model\Education\Content\EducationShowcaseItem;
use App\Model\Education\Content\EducationStageAchievementShowcase;

final class ShowcaseRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationStageAchievementShowcase
    {
        if (isset($data['id'])) {
            $showcase = EducationStageAchievementShowcase::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id']);
            $showcase->fill($data);
            $showcase->save();

            return $showcase;
        }

        return EducationStageAchievementShowcase::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationStageAchievementShowcase
    {
        return EducationStageAchievementShowcase::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function replaceItems(int $tenantId, ?int $campusId, int $showcaseId, array $items): void
    {
        EducationShowcaseItem::query()
            ->where('tenant_id', $tenantId)
            ->where('showcase_id', $showcaseId)
            ->delete();

        foreach ($items as $index => $item) {
            EducationShowcaseItem::query()->create($item + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'showcase_id' => $showcaseId,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageGuardian(int $tenantId, int $studentId): array
    {
        $query = EducationStageAchievementShowcase::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->where('status', 'published');

        return [
            'list' => $query->orderByDesc('published_at')->get()->toArray(),
            'total' => (int) (clone $query)->count(),
        ];
    }
}

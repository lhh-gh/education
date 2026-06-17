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

namespace App\Repository\Education\Family;

use App\Model\Education\Family\EducationGrowthRecord;

final class GrowthRecordRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationGrowthRecord
    {
        return EducationGrowthRecord::query()->create($data);
    }

    public function find(int $id, int $tenantId): ?EducationGrowthRecord
    {
        $row = EducationGrowthRecord::query()->where('tenant_id', $tenantId)->whereKey($id)->first();

        return $row instanceof EducationGrowthRecord ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publishedForStudent(int $tenantId, int $studentId): array
    {
        return EducationGrowthRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->get()
            ->map(static fn (EducationGrowthRecord $row): array => $row->toArray())
            ->all();
    }
}

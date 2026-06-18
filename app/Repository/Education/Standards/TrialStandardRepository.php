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

namespace App\Repository\Education\Standards;

use App\Model\Education\Standards\EducationTrialLessonStandard;
use App\Model\Education\Standards\EducationTrialLessonStandardItem;

final class TrialStandardRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationTrialLessonStandard
    {
        return EducationTrialLessonStandard::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'standard_code' => $data['standard_code'],
            'version_no' => $data['version_no'] ?? 1,
        ], $data + ['version_no' => 1, 'status' => 'draft']);
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function replaceItems(int $tenantId, int $campusId, int $standardId, array $items): void
    {
        EducationTrialLessonStandardItem::query()
            ->where('tenant_id', $tenantId)
            ->where('trial_lesson_standard_id', $standardId)
            ->delete();

        foreach ($items as $item) {
            EducationTrialLessonStandardItem::query()->create($item + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'trial_lesson_standard_id' => $standardId,
            ]);
        }
    }
}

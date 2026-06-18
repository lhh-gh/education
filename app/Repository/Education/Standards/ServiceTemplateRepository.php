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

use App\Model\Education\Standards\EducationServiceTemplateItem;
use App\Model\Education\Standards\EducationServiceTemplateSet;

final class ServiceTemplateRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveSet(array $data): EducationServiceTemplateSet
    {
        return EducationServiceTemplateSet::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'template_set_code' => $data['template_set_code'],
            'version_no' => $data['version_no'] ?? 1,
        ], $data + ['version_no' => 1, 'status' => 'draft']);
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function replaceItems(int $tenantId, int $campusId, int $setId, array $items): void
    {
        EducationServiceTemplateItem::query()->where('tenant_id', $tenantId)->where('template_set_id', $setId)->delete();
        foreach ($items as $item) {
            EducationServiceTemplateItem::query()->create($item + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'template_set_id' => $setId,
            ]);
        }
    }
}

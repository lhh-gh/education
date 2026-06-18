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

use App\Model\Education\Standards\EducationTeachingDeliveryStandard;

final class DeliveryStandardRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationTeachingDeliveryStandard
    {
        return EducationTeachingDeliveryStandard::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'standard_code' => $data['standard_code'],
            'version_no' => $data['version_no'] ?? 1,
        ], $data + ['version_no' => 1, 'status' => 'draft']);
    }
}

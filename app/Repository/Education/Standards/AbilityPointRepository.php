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

use App\Model\Education\Standards\EducationCourseAbilityPoint;

final class AbilityPointRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationCourseAbilityPoint
    {
        return EducationCourseAbilityPoint::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'ability_code' => $data['ability_code'],
        ], $data + ['status' => 'enabled']);
    }
}

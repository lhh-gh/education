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

namespace App\Service\Education\Standards;

use App\Repository\Education\Standards\AbilityPointRepository;

final class AbilityPointService
{
    public function __construct(private readonly AbilityPointRepository $abilities) {}

    /**
     * @param array<string, mixed> $data
     * @return array{ability_point_id: int, status: string}
     */
    public function save(array $data): array
    {
        $ability = $this->abilities->save($data);

        return ['ability_point_id' => (int) $ability->id, 'status' => (string) $ability->status];
    }
}

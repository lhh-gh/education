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

use App\Repository\Education\Standards\StageGoalRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class StageGoalService
{
    public function __construct(private readonly StageGoalRepository $goals) {}

    /**
     * @param array<string, mixed> $data
     * @return array{stage_goal_id: int, status: string}
     */
    public function save(array $data, ?EducationUserContext $context = null): array
    {
        $abilityPointIds = array_map('intval', $data['ability_point_ids'] ?? []);
        unset($data['ability_point_ids']);

        $goal = $this->goals->save($data + ['status' => 'draft', 'sort_order' => 0], $context);
        $this->goals->syncAbilityPoints((int) $goal->tenant_id, (int) ($goal->campus_id ?? 0), (int) $goal->id, $abilityPointIds);

        return ['stage_goal_id' => (int) $goal->id, 'status' => $this->statusValue($goal->status)];
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}

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

namespace App\Service\Education\Workflow;

use App\Repository\Education\Workflow\WorkflowRuleRepository;

final class WorkflowRuleService
{
    public function __construct(
        private readonly WorkflowRuleRepository $ruleRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{rule_id: int, status: string}
     */
    public function save(array $data): array
    {
        $rule = $this->ruleRepository->create($data + ['status' => 'disabled']);

        return ['rule_id' => (int) $rule->id, 'status' => (string) $rule->status->value];
    }
}

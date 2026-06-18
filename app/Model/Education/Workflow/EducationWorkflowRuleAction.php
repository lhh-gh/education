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

namespace App\Model\Education\Workflow;

use Hyperf\DbConnection\Model\Model;

class EducationWorkflowRuleAction extends Model
{
    protected ?string $table = 'edu_workflow_rule_actions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'rule_id', 'action_type', 'action_config_json',
        'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'rule_id' => 'integer', 'action_config_json' => 'array', 'sort_order' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

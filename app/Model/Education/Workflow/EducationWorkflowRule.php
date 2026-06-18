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

use App\Model\Enums\Education\Workflow\WorkflowRuleStatus;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationWorkflowRule extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_workflow_rules';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'rule_code', 'rule_name', 'event_type', 'status',
        'priority', 'dedupe_window_minutes', 'description', 'created_by', 'updated_by',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'status' => WorkflowRuleStatus::class, 'priority' => 'integer', 'dedupe_window_minutes' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}

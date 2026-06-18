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

class EducationWorkflowExecutionLog extends Model
{
    protected ?string $table = 'edu_workflow_execution_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'rule_id', 'event_type', 'dedupe_key', 'status',
        'result_json', 'error_message', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'rule_id' => 'integer', 'result_json' => 'array',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

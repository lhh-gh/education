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

use App\Model\Enums\Education\Workflow\OperationAlertStatus;
use Hyperf\DbConnection\Model\Model;

class EducationOperationAlert extends Model
{
    protected ?string $table = 'edu_operation_alerts';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'alert_no', 'alert_type', 'level', 'status',
        'title', 'content', 'source_type', 'source_id', 'dedupe_key', 'converted_task_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'status' => OperationAlertStatus::class, 'source_id' => 'integer', 'converted_task_id' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

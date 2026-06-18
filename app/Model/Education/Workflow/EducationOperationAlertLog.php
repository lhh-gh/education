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

class EducationOperationAlertLog extends Model
{
    protected ?string $table = 'edu_operation_alert_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'operation_alert_id', 'operator_id', 'action',
        'before_status', 'after_status', 'content', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'operation_alert_id' => 'integer', 'operator_id' => 'integer',
        'before_status' => OperationAlertStatus::class, 'after_status' => OperationAlertStatus::class,
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

namespace App\Model\Education\Group;

use Hyperf\DbConnection\Model\Model;

class EducationGroupOperationMetric extends Model
{
    protected ?string $table = 'edu_group_operation_metrics';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'org_unit_id', 'campus_count', 'student_count',
        'revenue_cents', 'consumed_credits', 'renewal_alert_count', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'metric_date' => 'date',
        'org_unit_id' => 'integer',
        'campus_count' => 'integer',
        'student_count' => 'integer',
        'revenue_cents' => 'integer',
        'renewal_alert_count' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

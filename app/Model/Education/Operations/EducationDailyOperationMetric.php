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

namespace App\Model\Education\Operations;

use Hyperf\DbConnection\Model\Model;

class EducationDailyOperationMetric extends Model
{
    protected ?string $table = 'edu_daily_operation_metrics';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'metric_date',
        'lessons_count',
        'pending_attendance_count',
        'consumed_credits',
        'present_count',
        'leave_count',
        'absent_count',
        'renewal_alert_count',
        'pending_review_count',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'metric_date' => 'date',
        'lessons_count' => 'integer',
        'pending_attendance_count' => 'integer',
        'consumed_credits' => 'decimal:2',
        'present_count' => 'integer',
        'leave_count' => 'integer',
        'absent_count' => 'integer',
        'renewal_alert_count' => 'integer',
        'pending_review_count' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

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

namespace App\Model\Education\Growth;

use Hyperf\DbConnection\Model\Model;

class EducationGrowthConsultantMetricDaily extends Model
{
    protected ?string $table = 'edu_growth_consultant_metrics_daily';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'consultant_user_id',
        'assigned_leads_count', 'follow_count', 'trial_count', 'converted_count',
        'lost_count', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'metric_date' => 'date', 'consultant_user_id' => 'integer',
        'assigned_leads_count' => 'integer', 'follow_count' => 'integer',
        'trial_count' => 'integer', 'converted_count' => 'integer', 'lost_count' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

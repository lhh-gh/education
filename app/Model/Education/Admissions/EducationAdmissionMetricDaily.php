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

namespace App\Model\Education\Admissions;

use Hyperf\DbConnection\Model\Model;

class EducationAdmissionMetricDaily extends Model
{
    protected ?string $table = 'edu_admission_metrics_daily';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'metric_date',
        'source_id',
        'consultant_user_id',
        'new_leads_count',
        'follow_count',
        'trial_count',
        'trial_attended_count',
        'converted_count',
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
        'source_id' => 'integer',
        'consultant_user_id' => 'integer',
        'new_leads_count' => 'integer',
        'follow_count' => 'integer',
        'trial_count' => 'integer',
        'trial_attended_count' => 'integer',
        'converted_count' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

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

class EducationGrowthChannelRoiDaily extends Model
{
    protected ?string $table = 'edu_growth_channel_roi_daily';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'source_id', 'lead_count',
        'converted_count', 'cost_cents', 'converted_revenue_cents', 'roi',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'metric_date' => 'date', 'source_id' => 'integer', 'lead_count' => 'integer',
        'converted_count' => 'integer', 'cost_cents' => 'integer',
        'converted_revenue_cents' => 'integer', 'roi' => 'decimal:4',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

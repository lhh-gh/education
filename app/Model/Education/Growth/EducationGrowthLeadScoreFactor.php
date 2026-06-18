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

class EducationGrowthLeadScoreFactor extends Model
{
    protected ?string $table = 'edu_growth_lead_score_factors';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lead_score_id', 'factor_code',
        'factor_name', 'factor_value', 'points', 'weight', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lead_score_id' => 'integer', 'points' => 'integer', 'weight' => 'decimal:4',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

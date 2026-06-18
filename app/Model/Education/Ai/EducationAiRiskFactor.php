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

namespace App\Model\Education\Ai;

use Hyperf\DbConnection\Model\Model;

class EducationAiRiskFactor extends Model
{
    protected ?string $table = 'edu_ai_risk_factors';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'risk_score_id', 'factor_code', 'factor_name', 'factor_value',
        'weight', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'risk_score_id' => 'integer',
        'weight' => 'decimal:4', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

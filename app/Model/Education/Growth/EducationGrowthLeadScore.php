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

use App\Model\Enums\Education\Growth\LeadScoreLevel;
use Hyperf\DbConnection\Model\Model;

class EducationGrowthLeadScore extends Model
{
    protected ?string $table = 'edu_growth_lead_scores';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lead_id', 'score_date', 'score',
        'score_level', 'stage', 'owner_user_id', 'summary', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lead_id' => 'integer', 'score_date' => 'date', 'score' => 'integer',
        'score_level' => LeadScoreLevel::class, 'owner_user_id' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

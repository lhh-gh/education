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
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationGrowthFollowupStrategy extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_growth_followup_strategies';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'strategy_code', 'strategy_name',
        'lead_stage', 'score_level', 'suggestion_template', 'next_follow_hours',
        'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'score_level' => LeadScoreLevel::class, 'next_follow_hours' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}

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

use App\Model\Enums\Education\Growth\SuggestionStatus;
use Hyperf\DbConnection\Model\Model;

class EducationGrowthFollowupSuggestion extends Model
{
    protected ?string $table = 'edu_growth_followup_suggestions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lead_id', 'strategy_id', 'owner_user_id',
        'suggestion_text', 'status', 'due_at', 'handled_at', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lead_id' => 'integer', 'strategy_id' => 'integer', 'owner_user_id' => 'integer',
        'status' => SuggestionStatus::class, 'due_at' => 'datetime', 'handled_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

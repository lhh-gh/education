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

use App\Model\Enums\Education\Ai\AiTaskStatus;
use Hyperf\DbConnection\Model\Model;

class EducationAiRecommendationTask extends Model
{
    protected ?string $table = 'edu_ai_recommendation_tasks';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'recommendation_type', 'target_type', 'target_id',
        'assignee_user_id', 'status', 'recommendation_json', 'handled_at', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'target_id' => 'integer',
        'assignee_user_id' => 'integer', 'status' => AiTaskStatus::class, 'recommendation_json' => 'array',
        'handled_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

class EducationAiGenerationTask extends Model
{
    protected ?string $table = 'edu_ai_generation_tasks';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'task_no', 'feature_code', 'model_config_id', 'prompt_template_id',
        'business_type', 'business_id', 'requester_user_id', 'status', 'context_hash', 'queued_at',
        'started_at', 'finished_at', 'error_message', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'model_config_id' => 'integer',
        'prompt_template_id' => 'integer', 'business_id' => 'integer', 'requester_user_id' => 'integer',
        'status' => AiTaskStatus::class, 'queued_at' => 'datetime', 'started_at' => 'datetime', 'finished_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

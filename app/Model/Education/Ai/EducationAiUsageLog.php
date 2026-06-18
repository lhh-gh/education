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

class EducationAiUsageLog extends Model
{
    protected ?string $table = 'edu_ai_usage_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'generation_task_id', 'provider', 'model_name', 'prompt_tokens',
        'completion_tokens', 'total_tokens', 'cost_cents', 'usage_date', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'generation_task_id' => 'integer',
        'prompt_tokens' => 'integer', 'completion_tokens' => 'integer', 'total_tokens' => 'integer', 'cost_cents' => 'integer',
        'usage_date' => 'date', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

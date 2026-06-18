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

use App\Model\Enums\Education\Ai\AiSafetyLevel;
use Hyperf\DbConnection\Model\Model;

class EducationAiSafetyEvent extends Model
{
    protected ?string $table = 'edu_ai_safety_events';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'generation_task_id', 'risk_level', 'event_type', 'summary',
        'payload_json', 'handled', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'generation_task_id' => 'integer',
        'risk_level' => AiSafetyLevel::class, 'payload_json' => 'array', 'handled' => 'boolean',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

use App\Model\Enums\Education\Growth\AiTalkScriptStatus;
use Hyperf\DbConnection\Model\Model;

class EducationGrowthAiTalkScript extends Model
{
    protected ?string $table = 'edu_growth_ai_talk_scripts';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lead_id', 'generation_task_id',
        'script_type', 'script_text', 'status', 'confirmed_by', 'confirmed_at',
        'masked_input_json', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lead_id' => 'integer', 'generation_task_id' => 'integer',
        'status' => AiTalkScriptStatus::class, 'confirmed_by' => 'integer',
        'confirmed_at' => 'datetime', 'masked_input_json' => 'array',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

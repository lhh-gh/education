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

use App\Model\Enums\Education\Ai\AiReviewStatus;
use App\Model\Enums\Education\Ai\AiSafetyLevel;
use Hyperf\DbConnection\Model\Model;

class EducationAiGenerationResult extends Model
{
    protected ?string $table = 'edu_ai_generation_results';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'generation_task_id', 'result_text', 'result_json', 'safety_status',
        'review_status', 'visible_to_guardian', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'generation_task_id' => 'integer',
        'result_json' => 'array', 'safety_status' => AiSafetyLevel::class, 'review_status' => AiReviewStatus::class,
        'visible_to_guardian' => 'boolean', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

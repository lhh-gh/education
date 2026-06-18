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

class EducationAiRiskScore extends Model
{
    protected ?string $table = 'edu_ai_risk_scores';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'student_id', 'course_account_id', 'score_date', 'risk_score',
        'risk_level', 'summary', 'generation_task_id', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'student_id' => 'integer',
        'course_account_id' => 'integer', 'score_date' => 'date', 'risk_score' => 'integer',
        'risk_level' => AiSafetyLevel::class, 'generation_task_id' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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
use Hyperf\DbConnection\Model\Model;

class EducationAiReviewLog extends Model
{
    protected ?string $table = 'edu_ai_review_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'generation_result_id', 'reviewer_user_id', 'review_action',
        'review_note', 'reviewed_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'generation_result_id' => 'integer',
        'reviewer_user_id' => 'integer', 'review_action' => AiReviewStatus::class, 'reviewed_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

namespace App\Model\Education\Admissions;

use Hyperf\DbConnection\Model\Model;

class EducationTrialFeedback extends Model
{
    protected ?string $table = 'edu_trial_feedbacks';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'trial_lesson_id',
        'lead_id',
        'feedback_type',
        'teacher_id',
        'consultant_user_id',
        'score',
        'content',
        'recommend_course_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'trial_lesson_id' => 'integer',
        'lead_id' => 'integer',
        'teacher_id' => 'integer',
        'consultant_user_id' => 'integer',
        'score' => 'integer',
        'recommend_course_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

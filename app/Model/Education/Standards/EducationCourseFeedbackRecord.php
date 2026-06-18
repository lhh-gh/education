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

namespace App\Model\Education\Standards;

use Hyperf\DbConnection\Model\Model;

class EducationCourseFeedbackRecord extends Model
{
    protected ?string $table = 'edu_course_feedback_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'course_id', 'standard_version_id',
        'feedback_type', 'score', 'content', 'source_type', 'source_id',
        'submitted_by', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'course_id' => 'integer', 'standard_version_id' => 'integer',
        'score' => 'integer', 'source_id' => 'integer', 'submitted_by' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

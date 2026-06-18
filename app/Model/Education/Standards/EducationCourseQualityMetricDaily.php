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

class EducationCourseQualityMetricDaily extends Model
{
    protected ?string $table = 'edu_course_quality_metrics_daily';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'course_id',
        'feedback_count', 'average_score', 'trial_feedback_count',
        'delivery_feedback_count', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'metric_date' => 'date', 'course_id' => 'integer',
        'feedback_count' => 'integer', 'average_score' => 'decimal:2',
        'trial_feedback_count' => 'integer', 'delivery_feedback_count' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

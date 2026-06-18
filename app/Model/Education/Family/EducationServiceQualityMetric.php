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

namespace App\Model\Education\Family;

use Hyperf\DbConnection\Model\Model;

class EducationServiceQualityMetric extends Model
{
    protected ?string $table = 'edu_service_quality_metrics';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'teacher_id', 'student_id', 'comment_count',
        'homework_review_count', 'report_count', 'message_response_minutes', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'metric_date' => 'date',
        'teacher_id' => 'integer',
        'student_id' => 'integer',
        'comment_count' => 'integer',
        'homework_review_count' => 'integer',
        'report_count' => 'integer',
        'message_response_minutes' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

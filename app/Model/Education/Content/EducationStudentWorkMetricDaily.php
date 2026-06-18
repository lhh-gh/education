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

namespace App\Model\Education\Content;

use Hyperf\DbConnection\Model\Model;

class EducationStudentWorkMetricDaily extends Model
{
    protected ?string $table = 'edu_student_work_metrics_daily';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_date', 'student_id', 'teacher_id',
        'created_count', 'published_count', 'showcase_count', 'guardian_read_count',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'metric_date' => 'date', 'student_id' => 'integer', 'teacher_id' => 'integer',
        'created_count' => 'integer', 'published_count' => 'integer',
        'showcase_count' => 'integer', 'guardian_read_count' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

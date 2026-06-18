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

class EducationCourseStandardPublishLog extends Model
{
    protected ?string $table = 'edu_course_standard_publish_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'standard_version_id', 'business_type',
        'business_id', 'from_status', 'to_status', 'operator_id', 'note',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'standard_version_id' => 'integer', 'business_id' => 'integer',
        'operator_id' => 'integer', 'created_by' => 'integer',
        'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

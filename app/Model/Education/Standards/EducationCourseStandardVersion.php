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

use App\Model\Enums\Education\Standards\StandardPublishStatus;
use Hyperf\DbConnection\Model\Model;

class EducationCourseStandardVersion extends Model
{
    protected ?string $table = 'edu_course_standard_versions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'business_type', 'business_id',
        'version_no', 'status', 'snapshot_json', 'published_by',
        'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'business_id' => 'integer', 'version_no' => 'integer',
        'status' => StandardPublishStatus::class, 'snapshot_json' => 'array',
        'published_by' => 'integer', 'published_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationCourseServicePackage extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_course_service_packages';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'package_code', 'package_name',
        'course_id', 'version_no', 'status', 'guardian_visible', 'description',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'course_id' => 'integer', 'version_no' => 'integer',
        'status' => StandardPublishStatus::class, 'guardian_visible' => 'boolean',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}

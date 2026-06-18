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

class EducationCourseLocalizationOverride extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_course_localization_overrides';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'standard_version_id', 'override_json',
        'status', 'published_at', 'created_by', 'updated_by', 'created_at',
        'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'standard_version_id' => 'integer', 'override_json' => 'array',
        'status' => StandardPublishStatus::class, 'published_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}

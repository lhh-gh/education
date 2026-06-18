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

class EducationServiceTemplateSet extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_service_template_sets';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'template_set_code', 'template_set_name',
        'course_id', 'status', 'version_no', 'created_by', 'updated_by',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'course_id' => 'integer', 'status' => StandardPublishStatus::class,
        'version_no' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}

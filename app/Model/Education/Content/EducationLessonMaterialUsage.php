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

class EducationLessonMaterialUsage extends Model
{
    protected ?string $table = 'edu_lesson_material_usages';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lesson_id', 'teacher_id', 'material_id',
        'material_version_id', 'usage_type', 'used_at', 'remark', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lesson_id' => 'integer', 'teacher_id' => 'integer', 'material_id' => 'integer',
        'material_version_id' => 'integer', 'used_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

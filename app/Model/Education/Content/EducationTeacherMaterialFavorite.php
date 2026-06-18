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

class EducationTeacherMaterialFavorite extends Model
{
    protected ?string $table = 'edu_teacher_material_favorites';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'teacher_id', 'material_id',
        'favorited_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'teacher_id' => 'integer', 'material_id' => 'integer',
        'favorited_at' => 'datetime', 'created_by' => 'integer',
        'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

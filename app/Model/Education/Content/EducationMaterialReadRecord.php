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

class EducationMaterialReadRecord extends Model
{
    protected ?string $table = 'edu_material_read_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'material_id', 'material_version_id',
        'student_id', 'guardian_user_id', 'teacher_id', 'read_at', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'material_id' => 'integer', 'material_version_id' => 'integer',
        'student_id' => 'integer', 'guardian_user_id' => 'integer',
        'teacher_id' => 'integer', 'read_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

namespace App\Model\Education\Group;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationUserDataPermission extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_user_data_permissions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'user_id', 'scope_id', 'scope_type', 'effective_start', 'effective_end',
        'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'user_id' => 'integer',
        'scope_id' => 'integer',
        'effective_start' => 'date',
        'effective_end' => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

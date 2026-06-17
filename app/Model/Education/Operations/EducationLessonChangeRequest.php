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

namespace App\Model\Education\Operations;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationLessonChangeRequest extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_change_requests';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lesson_id',
        'change_type',
        'status',
        'old_values_json',
        'new_values_json',
        'reason',
        'requested_by',
        'approved_by',
        'approved_at',
        'applied_at',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'lesson_id' => 'integer',
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'requested_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

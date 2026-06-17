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

class EducationMakeupEntitlement extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_makeup_entitlements';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'student_id',
        'course_id',
        'source_lesson_id',
        'source_leave_request_id',
        'status',
        'expires_at',
        'used_lesson_id',
        'used_at',
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
        'student_id' => 'integer',
        'course_id' => 'integer',
        'source_lesson_id' => 'integer',
        'source_leave_request_id' => 'integer',
        'expires_at' => 'datetime',
        'used_lesson_id' => 'integer',
        'used_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

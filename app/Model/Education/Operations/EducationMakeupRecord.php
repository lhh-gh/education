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

class EducationMakeupRecord extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_makeup_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'makeup_entitlement_id',
        'student_id',
        'makeup_lesson_id',
        'status',
        'arranged_by',
        'arranged_at',
        'completed_at',
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
        'makeup_entitlement_id' => 'integer',
        'student_id' => 'integer',
        'makeup_lesson_id' => 'integer',
        'arranged_by' => 'integer',
        'arranged_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

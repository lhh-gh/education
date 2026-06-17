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

namespace App\Model\Education\Admissions;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationAdmissionTask extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_admission_tasks';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lead_id',
        'task_type',
        'title',
        'assignee_user_id',
        'status',
        'due_at',
        'completed_at',
        'result',
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
        'lead_id' => 'integer',
        'assignee_user_id' => 'integer',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

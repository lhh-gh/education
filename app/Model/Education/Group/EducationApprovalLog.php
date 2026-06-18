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

use Hyperf\DbConnection\Model\Model;

class EducationApprovalLog extends Model
{
    protected ?string $table = 'edu_approval_logs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'approval_instance_id', 'task_id', 'operator_id', 'action',
        'before_status', 'after_status', 'comment', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'approval_instance_id' => 'integer',
        'task_id' => 'integer',
        'operator_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

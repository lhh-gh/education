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

class EducationApprovalTask extends Model
{
    protected ?string $table = 'edu_approval_tasks';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'approval_instance_id', 'node_id', 'assignee_user_id', 'status',
        'due_at', 'completed_at', 'result', 'comment', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'approval_instance_id' => 'integer',
        'node_id' => 'integer',
        'assignee_user_id' => 'integer',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

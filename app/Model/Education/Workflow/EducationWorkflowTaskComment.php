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

namespace App\Model\Education\Workflow;

use Hyperf\DbConnection\Model\Model;

class EducationWorkflowTaskComment extends Model
{
    protected ?string $table = 'edu_workflow_task_comments';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'workflow_task_id', 'commenter_user_id', 'content',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'workflow_task_id' => 'integer', 'commenter_user_id' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

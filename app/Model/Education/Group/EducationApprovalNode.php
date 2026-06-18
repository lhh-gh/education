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

class EducationApprovalNode extends Model
{
    protected ?string $table = 'edu_approval_nodes';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'template_id', 'node_code', 'node_name', 'sort_order', 'assignee_type',
        'assignee_value_json', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'template_id' => 'integer',
        'sort_order' => 'integer',
        'assignee_value_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

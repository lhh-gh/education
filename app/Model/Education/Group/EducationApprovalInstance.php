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

class EducationApprovalInstance extends Model
{
    protected ?string $table = 'edu_approval_instances';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'template_id', 'business_type', 'business_id', 'status',
        'current_node_id', 'initiator_id', 'payload_json', 'completed_at', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'template_id' => 'integer',
        'business_id' => 'integer',
        'current_node_id' => 'integer',
        'initiator_id' => 'integer',
        'payload_json' => 'array',
        'completed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

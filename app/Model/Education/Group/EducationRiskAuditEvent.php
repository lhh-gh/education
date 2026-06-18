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

class EducationRiskAuditEvent extends Model
{
    protected ?string $table = 'edu_risk_audit_events';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'event_type', 'risk_level', 'business_type', 'business_id',
        'operator_id', 'summary', 'payload_json', 'handled', 'handled_by', 'handled_at',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'business_id' => 'integer',
        'operator_id' => 'integer',
        'payload_json' => 'array',
        'handled' => 'boolean',
        'handled_by' => 'integer',
        'handled_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

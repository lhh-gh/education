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

namespace App\Model\Education\Foundation;

use Hyperf\DbConnection\Model\Model;

class EducationAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected ?string $table = 'edu_audit_logs';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'actor_user_id',
        'actor_type',
        'actor_role_code',
        'module',
        'resource',
        'action',
        'business_type',
        'business_id',
        'request_id',
        'ip_address',
        'user_agent',
        'method',
        'path',
        'summary',
        'before_snapshot',
        'after_snapshot',
        'diff',
        'metadata',
        'created_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'actor_user_id' => 'integer',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'diff' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}

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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationContract extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_contracts';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'contract_no', 'contract_type', 'title', 'counterparty_name',
        'amount_cents', 'status', 'start_date', 'end_date', 'owner_user_id', 'risk_level',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'amount_cents' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'owner_user_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

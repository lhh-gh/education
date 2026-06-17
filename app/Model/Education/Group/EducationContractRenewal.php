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

class EducationContractRenewal extends Model
{
    protected ?string $table = 'edu_contract_renewals';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'contract_id', 'renewal_type', 'status', 'due_date', 'handled_by',
        'handled_at', 'result', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'contract_id' => 'integer',
        'due_date' => 'date',
        'handled_by' => 'integer',
        'handled_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

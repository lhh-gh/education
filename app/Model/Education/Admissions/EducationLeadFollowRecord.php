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

use Hyperf\DbConnection\Model\Model;

class EducationLeadFollowRecord extends Model
{
    protected ?string $table = 'edu_lead_follow_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lead_id',
        'follow_type',
        'content',
        'next_follow_at',
        'result',
        'operator_user_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'lead_id' => 'integer',
        'next_follow_at' => 'datetime',
        'operator_user_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

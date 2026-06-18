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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationLead extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_leads';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lead_no',
        'source_id',
        'contact_name',
        'contact_mobile',
        'contact_wechat',
        'stage',
        'status',
        'owner_user_id',
        'intention_course_id',
        'intention_level',
        'next_follow_at',
        'last_follow_at',
        'remark',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'source_id' => 'integer',
        'owner_user_id' => 'integer',
        'intention_course_id' => 'integer',
        'next_follow_at' => 'datetime',
        'last_follow_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

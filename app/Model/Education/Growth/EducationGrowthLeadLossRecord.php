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

namespace App\Model\Education\Growth;

use Hyperf\DbConnection\Model\Model;

class EducationGrowthLeadLossRecord extends Model
{
    protected ?string $table = 'edu_growth_lead_loss_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lead_id', 'loss_reason_id',
        'lost_by', 'lost_at', 'detail', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'lead_id' => 'integer', 'loss_reason_id' => 'integer', 'lost_by' => 'integer',
        'lost_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

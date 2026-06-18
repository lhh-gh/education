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

class EducationGrowthChannelCost extends Model
{
    protected ?string $table = 'edu_growth_channel_costs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'source_id', 'cost_date', 'cost_type',
        'amount_cents', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'source_id' => 'integer', 'cost_date' => 'date', 'amount_cents' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

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

namespace App\Model\Education\Finance;

use Hyperf\DbConnection\Model\Model;

class EducationFinanceOrderItem extends Model
{
    protected ?string $table = 'edu_finance_order_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'order_id', 'item_type', 'item_name', 'quantity',
        'unit_amount_cents', 'total_amount_cents', 'source_type', 'source_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'order_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_amount_cents' => 'integer',
        'total_amount_cents' => 'integer',
        'source_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

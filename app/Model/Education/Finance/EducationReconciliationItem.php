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

class EducationReconciliationItem extends Model
{
    protected ?string $table = 'edu_reconciliation_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'batch_id', 'channel_trade_no', 'payment_record_id',
        'amount_cents', 'trade_time', 'match_status', 'difference_cents', 'raw_row_json',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'batch_id' => 'integer',
        'payment_record_id' => 'integer',
        'amount_cents' => 'integer',
        'trade_time' => 'datetime',
        'difference_cents' => 'integer',
        'raw_row_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

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

class EducationPaymentRecord extends Model
{
    protected ?string $table = 'edu_payment_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'order_id', 'payment_no', 'channel_code',
        'channel_trade_no', 'amount_cents', 'channel_fee_cents', 'status', 'paid_at',
        'payer_name', 'operator_id', 'remark', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'order_id' => 'integer',
        'amount_cents' => 'integer',
        'channel_fee_cents' => 'integer',
        'paid_at' => 'datetime',
        'operator_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

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

class EducationRefundRecord extends Model
{
    protected ?string $table = 'edu_refund_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'refund_request_id', 'payment_record_id', 'refund_trade_no',
        'refund_amount_cents', 'status', 'refunded_at', 'raw_payload_json',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'refund_request_id' => 'integer',
        'payment_record_id' => 'integer',
        'refund_amount_cents' => 'integer',
        'refunded_at' => 'datetime',
        'raw_payload_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

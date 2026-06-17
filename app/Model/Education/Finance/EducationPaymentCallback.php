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

class EducationPaymentCallback extends Model
{
    protected ?string $table = 'edu_payment_callbacks';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'channel_code', 'channel_trade_no', 'payment_record_id',
        'raw_payload_json', 'signature_valid', 'processed', 'processed_at', 'error_message',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'payment_record_id' => 'integer',
        'raw_payload_json' => 'array',
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

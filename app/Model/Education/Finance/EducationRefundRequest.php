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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationRefundRequest extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_refund_requests';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'order_id', 'payment_record_id', 'refund_no',
        'refund_amount_cents', 'reason', 'status', 'requested_by', 'reviewed_by', 'reviewed_at',
        'review_note', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'order_id' => 'integer',
        'payment_record_id' => 'integer',
        'refund_amount_cents' => 'integer',
        'requested_by' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

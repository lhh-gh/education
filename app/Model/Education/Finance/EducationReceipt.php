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

class EducationReceipt extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_receipts';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'receipt_no', 'order_id', 'student_id', 'amount_cents',
        'status', 'issued_by', 'issued_at', 'voided_by', 'voided_at', 'pdf_url',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'order_id' => 'integer',
        'student_id' => 'integer',
        'amount_cents' => 'integer',
        'issued_by' => 'integer',
        'issued_at' => 'datetime',
        'voided_by' => 'integer',
        'voided_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

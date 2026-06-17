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

class EducationFinanceOrder extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_finance_orders';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'order_no', 'order_type', 'student_id', 'guardian_id',
        'enrollment_id', 'student_course_account_id', 'total_amount_cents', 'paid_amount_cents',
        'refund_amount_cents', 'discount_amount_cents', 'status', 'due_at', 'paid_at', 'remark',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'student_id' => 'integer',
        'guardian_id' => 'integer',
        'enrollment_id' => 'integer',
        'student_course_account_id' => 'integer',
        'total_amount_cents' => 'integer',
        'paid_amount_cents' => 'integer',
        'refund_amount_cents' => 'integer',
        'discount_amount_cents' => 'integer',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

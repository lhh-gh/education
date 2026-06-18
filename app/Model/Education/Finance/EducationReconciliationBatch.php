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

class EducationReconciliationBatch extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_reconciliation_batches';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'batch_no', 'channel_code', 'business_date', 'status',
        'total_count', 'matched_count', 'unmatched_count', 'total_amount_cents',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'business_date' => 'date',
        'total_count' => 'integer',
        'matched_count' => 'integer',
        'unmatched_count' => 'integer',
        'total_amount_cents' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

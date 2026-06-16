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

namespace App\Model\Education\Academic;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationNotice extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_notices';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'notice_no',
        'notice_type',
        'target_type',
        'target_id',
        'title',
        'content',
        'priority',
        'status',
        'published_at',
        'published_by',
        'withdrawn_at',
        'withdrawn_by',
        'withdraw_reason',
        'expire_at',
        'receipt_count',
        'read_count',
        'remark',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'target_id' => 'integer',
        'published_at' => 'datetime',
        'published_by' => 'integer',
        'withdrawn_at' => 'datetime',
        'withdrawn_by' => 'integer',
        'expire_at' => 'datetime',
        'receipt_count' => 'integer',
        'read_count' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(EducationNoticeReceipt::class, 'notice_id', 'id');
    }
}

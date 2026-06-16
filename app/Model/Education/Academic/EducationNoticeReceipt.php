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

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationNoticeReceipt extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_notice_receipts';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'notice_id',
        'guardian_id',
        'student_id',
        'relation',
        'guardian_name_snapshot',
        'student_name_snapshot',
        'status',
        'delivered_at',
        'read_at',
        'read_by_profile_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'notice_id' => 'integer',
        'guardian_id' => 'integer',
        'student_id' => 'integer',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'read_by_profile_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(EducationNotice::class, 'notice_id', 'id');
    }
}

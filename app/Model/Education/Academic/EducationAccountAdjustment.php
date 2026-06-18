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

class EducationAccountAdjustment extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_account_adjustments';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'adjustment_no',
        'account_id',
        'student_id',
        'course_id',
        'adjustment_type',
        'direction',
        'units',
        'before_available_units',
        'after_available_units',
        'before_adjusted_units',
        'after_adjusted_units',
        'status',
        'original_adjustment_id',
        'rolled_back_at',
        'rolled_back_by',
        'reason',
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
        'account_id' => 'integer',
        'student_id' => 'integer',
        'course_id' => 'integer',
        'units' => 'decimal:2',
        'before_available_units' => 'decimal:2',
        'after_available_units' => 'decimal:2',
        'before_adjusted_units' => 'decimal:2',
        'after_adjusted_units' => 'decimal:2',
        'original_adjustment_id' => 'integer',
        'rolled_back_at' => 'datetime',
        'rolled_back_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }

    public function originalAdjustment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_adjustment_id', 'id');
    }
}

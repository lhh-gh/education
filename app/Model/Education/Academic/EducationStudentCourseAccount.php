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

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationStudentCourseAccount extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_student_course_accounts';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'student_id',
        'course_id',
        'purchased_units',
        'bonus_units',
        'consumed_units',
        'adjusted_units',
        'refunded_units',
        'frozen_units',
        'available_units',
        'status',
        'first_enrollment_id',
        'last_enrollment_id',
        'opened_at',
        'expires_at',
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
        'student_id' => 'integer',
        'course_id' => 'integer',
        'purchased_units' => 'decimal:2',
        'bonus_units' => 'decimal:2',
        'consumed_units' => 'decimal:2',
        'adjusted_units' => 'decimal:2',
        'refunded_units' => 'decimal:2',
        'frozen_units' => 'decimal:2',
        'available_units' => 'decimal:2',
        'first_enrollment_id' => 'integer',
        'last_enrollment_id' => 'integer',
        'opened_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(EducationTenant::class, 'tenant_id', 'id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(EducationCampus::class, 'campus_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(EducationStudent::class, 'student_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'course_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(EducationEnrollment::class, 'account_id', 'id');
    }
}

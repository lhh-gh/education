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
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationEnrollment extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_enrollments';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'enrollment_no',
        'student_id',
        'course_id',
        'lesson_package_id',
        'account_id',
        'student_name_snapshot',
        'course_name_snapshot',
        'package_name_snapshot',
        'package_lesson_units',
        'package_bonus_units',
        'total_units',
        'list_price',
        'deal_amount',
        'status',
        'enrolled_at',
        'confirmed_at',
        'materialized_at',
        'cancelled_at',
        'cancel_reason',
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
        'lesson_package_id' => 'integer',
        'account_id' => 'integer',
        'package_lesson_units' => 'decimal:2',
        'package_bonus_units' => 'decimal:2',
        'total_units' => 'decimal:2',
        'list_price' => 'decimal:2',
        'deal_amount' => 'decimal:2',
        'enrolled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'materialized_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function lessonPackage(): BelongsTo
    {
        return $this->belongsTo(EducationLessonPackage::class, 'lesson_package_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }
}

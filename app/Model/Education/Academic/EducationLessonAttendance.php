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
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationLessonAttendance extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_attendances';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lesson_id',
        'lesson_student_id',
        'class_id',
        'course_id',
        'student_id',
        'account_id',
        'attendance_status',
        'consume_policy',
        'planned_units',
        'consumed_units',
        'consumption_status',
        'submitted_at',
        'submitted_by',
        'attendance_batch_no',
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
        'lesson_id' => 'integer',
        'lesson_student_id' => 'integer',
        'class_id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'account_id' => 'integer',
        'planned_units' => 'decimal:2',
        'consumed_units' => 'decimal:2',
        'submitted_at' => 'datetime',
        'submitted_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EducationLesson::class, 'lesson_id', 'id');
    }

    public function lessonStudent(): BelongsTo
    {
        return $this->belongsTo(EducationLessonStudent::class, 'lesson_student_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }

    public function consumption(): HasOne
    {
        return $this->hasOne(EducationLessonConsumption::class, 'attendance_id', 'id');
    }
}

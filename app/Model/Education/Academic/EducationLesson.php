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

class EducationLesson extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lessons';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lesson_no',
        'class_id',
        'course_id',
        'teacher_id',
        'classroom_id',
        'title',
        'start_at',
        'end_at',
        'duration_minutes',
        'lesson_units',
        'student_count',
        'status',
        'source_type',
        'schedule_batch_no',
        'class_name_snapshot',
        'course_name_snapshot',
        'teacher_name_snapshot',
        'classroom_name_snapshot',
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
        'class_id' => 'integer',
        'course_id' => 'integer',
        'teacher_id' => 'integer',
        'classroom_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration_minutes' => 'integer',
        'lesson_units' => 'decimal:2',
        'student_count' => 'integer',
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

    public function educationClass(): BelongsTo
    {
        return $this->belongsTo(EducationClass::class, 'class_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'course_id', 'id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(EducationTeacher::class, 'teacher_id', 'id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(EducationClassroom::class, 'classroom_id', 'id');
    }

    public function lessonStudents(): HasMany
    {
        return $this->hasMany(EducationLessonStudent::class, 'lesson_id', 'id');
    }
}

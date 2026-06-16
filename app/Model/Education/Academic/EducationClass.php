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

class EducationClass extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_classes';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'course_id',
        'main_teacher_id',
        'classroom_id',
        'code',
        'name',
        'class_type',
        'max_students',
        'start_date',
        'end_date',
        'lesson_units',
        'status',
        'schedule_note',
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
        'course_id' => 'integer',
        'main_teacher_id' => 'integer',
        'classroom_id' => 'integer',
        'max_students' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'lesson_units' => 'decimal:2',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'course_id', 'id');
    }

    public function mainTeacher(): BelongsTo
    {
        return $this->belongsTo(EducationTeacher::class, 'main_teacher_id', 'id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(EducationClassroom::class, 'classroom_id', 'id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(EducationClassStudent::class, 'class_id', 'id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(EducationLesson::class, 'class_id', 'id');
    }
}

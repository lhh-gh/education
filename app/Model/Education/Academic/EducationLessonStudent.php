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

class EducationLessonStudent extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_students';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lesson_id',
        'class_id',
        'course_id',
        'student_id',
        'account_id',
        'student_name_snapshot',
        'student_no_snapshot',
        'lesson_units',
        'status',
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
        'class_id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'account_id' => 'integer',
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

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EducationLesson::class, 'lesson_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(EducationStudent::class, 'student_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }
}

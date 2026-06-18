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

class EducationLeaveRequest extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_leave_requests';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'leave_no',
        'source',
        'leave_type',
        'lesson_id',
        'lesson_student_id',
        'class_id',
        'course_id',
        'student_id',
        'account_id',
        'guardian_id',
        'teacher_id',
        'reason',
        'status',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'review_remark',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'makeup_required',
        'makeup_lesson_id',
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
        'guardian_id' => 'integer',
        'teacher_id' => 'integer',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'reviewed_by' => 'integer',
        'cancelled_at' => 'datetime',
        'cancelled_by' => 'integer',
        'makeup_required' => 'boolean',
        'makeup_lesson_id' => 'integer',
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

    public function makeupLesson(): BelongsTo
    {
        return $this->belongsTo(EducationLesson::class, 'makeup_lesson_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }
}

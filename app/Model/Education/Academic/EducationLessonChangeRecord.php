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

class EducationLessonChangeRecord extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_change_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'change_no',
        'change_type',
        'status',
        'leave_request_id',
        'source_lesson_id',
        'source_lesson_student_id',
        'target_lesson_id',
        'class_id',
        'course_id',
        'student_id',
        'account_id',
        'source_teacher_id',
        'target_teacher_id',
        'source_classroom_id',
        'target_classroom_id',
        'source_start_at',
        'source_end_at',
        'target_start_at',
        'target_end_at',
        'lesson_units',
        'reason',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
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
        'leave_request_id' => 'integer',
        'source_lesson_id' => 'integer',
        'source_lesson_student_id' => 'integer',
        'target_lesson_id' => 'integer',
        'class_id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'account_id' => 'integer',
        'source_teacher_id' => 'integer',
        'target_teacher_id' => 'integer',
        'source_classroom_id' => 'integer',
        'target_classroom_id' => 'integer',
        'source_start_at' => 'datetime',
        'source_end_at' => 'datetime',
        'target_start_at' => 'datetime',
        'target_end_at' => 'datetime',
        'lesson_units' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'cancelled_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(EducationLeaveRequest::class, 'leave_request_id', 'id');
    }

    public function sourceLesson(): BelongsTo
    {
        return $this->belongsTo(EducationLesson::class, 'source_lesson_id', 'id');
    }

    public function targetLesson(): BelongsTo
    {
        return $this->belongsTo(EducationLesson::class, 'target_lesson_id', 'id');
    }
}

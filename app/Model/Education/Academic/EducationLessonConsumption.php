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

class EducationLessonConsumption extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_consumptions';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'consumption_no',
        'account_id',
        'student_id',
        'course_id',
        'lesson_id',
        'lesson_student_id',
        'attendance_id',
        'source_type',
        'direction',
        'units',
        'before_available_units',
        'after_available_units',
        'before_consumed_units',
        'after_consumed_units',
        'status',
        'original_consumption_id',
        'reversed_at',
        'reversed_by',
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
        'lesson_id' => 'integer',
        'lesson_student_id' => 'integer',
        'attendance_id' => 'integer',
        'units' => 'decimal:2',
        'before_available_units' => 'decimal:2',
        'after_available_units' => 'decimal:2',
        'before_consumed_units' => 'decimal:2',
        'after_consumed_units' => 'decimal:2',
        'original_consumption_id' => 'integer',
        'reversed_at' => 'datetime',
        'reversed_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(EducationLessonAttendance::class, 'attendance_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EducationStudentCourseAccount::class, 'account_id', 'id');
    }

    public function originalConsumption(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_consumption_id', 'id');
    }
}

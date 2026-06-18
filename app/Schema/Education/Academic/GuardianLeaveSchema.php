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

namespace App\Schema\Education\Academic;

use App\Model\Education\Academic\EducationLeaveRequest;

final class GuardianLeaveSchema
{
    public function leave(EducationLeaveRequest $leave): array
    {
        return [
            'id' => (int) $leave->id,
            'leave_no' => (string) $leave->leave_no,
            'source' => (string) $leave->source,
            'leave_type' => (string) $leave->leave_type,
            'lesson_id' => (int) $leave->lesson_id,
            'lesson_student_id' => (int) $leave->lesson_student_id,
            'class_id' => (int) $leave->class_id,
            'course_id' => (int) $leave->course_id,
            'student_id' => (int) $leave->student_id,
            'account_id' => (int) $leave->account_id,
            'guardian_id' => $leave->guardian_id === null ? null : (int) $leave->guardian_id,
            'reason' => (string) $leave->reason,
            'status' => (string) $leave->status,
            'requested_at' => $leave->requested_at?->toDateTimeString(),
            'makeup_required' => (bool) $leave->makeup_required,
            'created_at' => $leave->created_at?->toDateTimeString(),
        ];
    }
}

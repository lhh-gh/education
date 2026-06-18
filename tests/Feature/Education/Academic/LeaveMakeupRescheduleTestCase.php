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

namespace HyperfTests\Feature\Education\Academic;

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;

abstract class LeaveMakeupRescheduleTestCase extends ProfileRecordAdminCase
{
    protected function fixture(string $lessonStatus = 'scheduled'): array
    {
        $tenant = $this->tenant(uniqid('tenant_'));
        $campus = $this->campus($tenant, uniqid('main_'));
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => uniqid('ART'), 'name' => 'Art Basics', 'status' => 'enabled']);
        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'teacher_no' => uniqid('T'), 'name' => 'Teacher', 'status' => 'enabled']);
        EducationTeacherCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'status' => 'enabled', 'authorized_at' => '2026-06-01 00:00:00']);
        $classroom = EducationClassroom::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => uniqid('R'), 'name' => 'Room A', 'capacity' => 20, 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'main_teacher_id' => $teacher->id, 'classroom_id' => $classroom->id, 'code' => uniqid('C'), 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => uniqid('S'), 'name' => 'Student', 'status' => 'enabled']);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        $lesson = EducationLesson::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_no' => uniqid('L'), 'class_id' => $class->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'classroom_id' => $classroom->id, 'title' => 'Drawing', 'start_at' => '2026-06-16 09:00:00', 'end_at' => '2026-06-16 10:00:00', 'duration_minutes' => 60, 'lesson_units' => '1.00', 'student_count' => 1, 'status' => $lessonStatus, 'source_type' => 'manual', 'class_name_snapshot' => 'Sunday Art', 'course_name_snapshot' => 'Art Basics', 'teacher_name_snapshot' => 'Teacher', 'classroom_name_snapshot' => 'Room A']);
        $lessonStudent = EducationLessonStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'lesson_id' => $lesson->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => $student->name, 'student_no_snapshot' => $student->student_no, 'lesson_units' => '1.00', 'status' => 'planned']);

        return compact('tenant', 'campus', 'course', 'teacher', 'classroom', 'class', 'student', 'account', 'lesson', 'lessonStudent');
    }

    protected function createLeave(array $fixture, string $status = 'pending'): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $fixture['tenant']->id,
            'campus_id' => $fixture['campus']->id,
            'leave_no' => uniqid('LEA'),
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $fixture['lesson']->id,
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'class_id' => $fixture['class']->id,
            'course_id' => $fixture['course']->id,
            'student_id' => $fixture['student']->id,
            'account_id' => $fixture['account']->id,
            'teacher_id' => $fixture['teacher']->id,
            'reason' => 'Sick leave',
            'status' => $status,
            'makeup_required' => true,
        ]);
    }
}

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
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;

trait AcademicReportApiFixture
{
    protected function reportFixture(string $code = 'report_api'): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S-' . strtoupper($code),
            'name' => 'Student ' . $code,
            'status' => 'enabled',
        ]);
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'guardian_no' => 'G-' . strtoupper($code),
            'name' => 'Guardian ' . $code,
            'mobile' => '13900000001',
            'status' => 'enabled',
        ]);
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'relation' => 'mother',
            'is_primary' => true,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_no' => 'T-' . strtoupper($code),
            'name' => 'Teacher ' . $code,
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'C-' . strtoupper($code),
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $package = EducationLessonPackage::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'PKG-' . strtoupper($code),
            'name' => '20 Lessons',
            'lesson_units' => '20.00',
            'bonus_units' => '0.00',
            'total_units' => '20.00',
            'list_price' => '3000.00',
            'sale_price' => '2800.00',
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'CLS-' . strtoupper($code),
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'purchased_units' => '20.00',
            'bonus_units' => '0.00',
            'consumed_units' => '1.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '19.00',
            'status' => 'active',
            'opened_at' => '2026-06-01 00:00:00',
            'expires_at' => '2026-12-31 23:59:59',
        ]);
        $enrollment = EducationEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'enrollment_no' => 'ENR-' . strtoupper($code),
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
            'account_id' => $account->id,
            'student_name_snapshot' => $student->name,
            'course_name_snapshot' => $course->name,
            'package_name_snapshot' => $package->name,
            'package_lesson_units' => '20.00',
            'package_bonus_units' => '0.00',
            'total_units' => '20.00',
            'list_price' => '3000.00',
            'deal_amount' => '2800.00',
            'status' => 'confirmed',
            'confirmed_at' => '2026-06-01 10:00:00',
            'materialized_at' => '2026-06-01 10:00:00',
        ]);
        EducationClassStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'student_name_snapshot' => $student->name,
            'student_no_snapshot' => $student->student_no,
            'status' => 'active',
        ]);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'LES-' . strtoupper($code),
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Drawing Basics',
            'start_at' => '2026-06-12 09:00:00',
            'end_at' => '2026-06-12 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'completed',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher ' . $code,
        ]);
        $lessonStudent = EducationLessonStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => $lesson->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'student_name_snapshot' => $student->name,
            'student_no_snapshot' => $student->student_no,
            'lesson_units' => '1.00',
            'status' => 'attended',
        ]);
        $attendance = EducationLessonAttendance::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => $lesson->id,
            'lesson_student_id' => $lessonStudent->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'teacher_id' => $teacher->id,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'planned_units' => '1.00',
            'consumed_units' => '1.00',
            'consumption_status' => 'active',
            'submitted_at' => '2026-06-12 10:00:00',
            'submitted_by' => 1,
            'attendance_batch_no' => 'ATT-' . strtoupper($code),
        ]);
        $consumption = EducationLessonConsumption::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'consumption_no' => 'CON-' . strtoupper($code),
            'account_id' => $account->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'lesson_student_id' => $lessonStudent->id,
            'attendance_id' => $attendance->id,
            'source_type' => 'attendance',
            'direction' => 'decrease',
            'units' => '1.00',
            'before_available_units' => '20.00',
            'after_available_units' => '19.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => '1.00',
            'status' => 'active',
            'created_at' => '2026-06-12 10:05:00',
        ]);
        $leave = EducationLeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'leave_no' => 'LEA-' . strtoupper($code),
            'source' => 'guardian',
            'leave_type' => 'sick',
            'lesson_id' => $lesson->id,
            'lesson_student_id' => $lessonStudent->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'guardian_id' => $guardian->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Sick leave',
            'status' => 'pending',
            'requested_at' => '2026-06-12 08:00:00',
            'makeup_required' => true,
        ]);
        $notice = EducationNotice::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'notice_no' => 'NOT-' . strtoupper($code),
            'notice_type' => 'academic',
            'target_type' => 'student',
            'target_id' => $student->id,
            'title' => 'Class reminder',
            'content' => 'Bring pencils',
            'priority' => 'normal',
            'status' => 'published',
            'published_at' => '2026-06-12 08:30:00',
            'receipt_count' => 1,
            'read_count' => 0,
        ]);
        EducationNoticeReceipt::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'notice_id' => $notice->id,
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relation' => 'mother',
            'guardian_name_snapshot' => $guardian->name,
            'student_name_snapshot' => $student->name,
            'status' => 'unread',
            'delivered_at' => '2026-06-12 08:31:00',
        ]);

        return compact('tenant', 'campus', 'student', 'guardian', 'teacher', 'course', 'package', 'class', 'account', 'enrollment', 'lesson', 'lessonStudent', 'attendance', 'consumption', 'leave', 'notice');
    }

    protected function reportRangeParams(array $extra = []): array
    {
        return array_merge([
            'page' => 1,
            'pageSize' => 20,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $extra);
    }

    protected function scopedCampus(EducationTenant $tenant, string $code = 'west'): EducationCampus
    {
        return $this->campus($tenant, $code);
    }
}

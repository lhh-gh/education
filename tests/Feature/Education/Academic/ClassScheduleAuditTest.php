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

use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class ClassScheduleAuditTest extends ProfileRecordAdminCase
{
    public function testClassScheduleWritesAuditLogs(): void
    {
        $this->grantPermissions('education:academic:class:create', 'education:academic:class-student:save', 'education:academic:lesson-schedule:create', 'education:academic:lesson:update', 'education:academic:lesson:cancel');
        [$tenant, $campus, $course, $teacher, $classroom, $student] = $this->fixture();

        $class = $this->post('/admin/education/academic/classes', [
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'main_teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'lesson_units' => 1,
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $class['code']);

        $this->put('/admin/education/academic/classes/' . $class['data']['id'] . '/students', ['student_ids' => [$student->id]], $this->tenantHeaders($tenant));
        $lesson = $this->post('/admin/education/academic/lesson-schedule/single', [
            'class_id' => $class['data']['id'],
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'title' => 'Drawing',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/lessons/' . $lesson['data']['lesson']['id'], ['lesson_units' => 1.5], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/lessons/' . $lesson['data']['lesson']['id'] . '/cancel', ['cancel_reason' => 'Audit'], $this->tenantHeaders($tenant));

        foreach ([
            'education.academic.class.created',
            'education.academic.class_student.saved',
            'education.academic.lesson.scheduled',
            'education.academic.lesson.updated',
            'education.academic.lesson.cancelled',
        ] as $action) {
            self::assertTrue(EducationAuditLog::query()->where('action', $action)->exists(), "missing audit {$action}");
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'teacher_no' => 'T001', 'name' => 'Teacher Wang', 'status' => 'enabled']);
        EducationTeacherCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'status' => 'enabled']);
        $classroom = EducationClassroom::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'R001', 'name' => 'Room 1', 'capacity' => 20, 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'available_units' => '10.00', 'status' => 'active']);

        return [$tenant, $campus, $course, $teacher, $classroom, $student];
    }
}

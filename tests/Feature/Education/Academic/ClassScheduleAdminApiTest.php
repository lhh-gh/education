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

/**
 * @internal
 * @coversNothing
 */
final class ClassScheduleAdminApiTest extends ProfileRecordAdminCase
{
    public function testClassStudentScheduleAndLessonWorkflowReturnsMineadminShape(): void
    {
        $this->grantPermissions(
            'education:academic:class:create',
            'education:academic:class:page',
            'education:academic:class-student:save',
            'education:academic:class-student:page',
            'education:academic:lesson-schedule:create',
            'education:academic:lesson-schedule:calendar',
            'education:academic:lesson:page',
            'education:academic:lesson:detail',
            'education:academic:lesson:update',
            'education:academic:lesson:cancel'
        );
        [$tenant, $campus, $course, $teacher, $classroom, $student] = $this->fixture();

        $class = $this->post('/admin/education/academic/classes', [
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'main_teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => 1,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $class['code']);

        $saveStudents = $this->put('/admin/education/academic/classes/' . $class['data']['id'] . '/students', [
            'student_ids' => [$student->id],
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $saveStudents['code']);
        self::assertSame(1, $saveStudents['data']['active_count']);

        $single = $this->post('/admin/education/academic/lesson-schedule/single', [
            'class_id' => $class['data']['id'],
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'title' => 'Drawing',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'lesson_units' => 1,
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $single['code']);
        self::assertSame(1, $single['data']['lesson']['student_count']);
        self::assertSame(1, $single['data']['lesson_students']['created_count']);

        $calendar = $this->get('/admin/education/academic/lesson-schedule/calendar', [
            'token' => $this->token,
            'campus_id' => $campus->id,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(1, \count($calendar['data']['list']));

        $lessonId = $single['data']['lesson']['id'];
        $detail = $this->get('/admin/education/academic/lessons/' . $lessonId, ['token' => $this->token], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(ResultCode::SUCCESS->value, $detail['code']);
        self::assertSame($student->id, $detail['data']['students'][0]['student_id']);

        $updated = $this->put('/admin/education/academic/lessons/' . $lessonId, [
            'lesson_units' => 1.5,
        ], $this->tenantHeaders($tenant));
        self::assertSame('1.50', $updated['data']['lesson_units']);

        $cancelled = $this->put('/admin/education/academic/lessons/' . $lessonId . '/cancel', [
            'cancel_reason' => 'Rain',
        ], $this->tenantHeaders($tenant));
        self::assertSame('cancelled', $cancelled['data']['status']);
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

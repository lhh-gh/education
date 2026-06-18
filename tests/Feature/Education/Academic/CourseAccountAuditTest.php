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
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class CourseAccountAuditTest extends ProfileRecordAdminCase
{
    public function testCourseAccountWritesCreateAuditLogs(): void
    {
        $this->grantPermissions(
            'education:academic:course:create',
            'education:academic:course-teacher:save',
            'education:academic:lesson-package:create',
            'education:academic:enrollment:create',
            'education:academic:enrollment:cancel',
            'education:academic:student-course-account:status'
        );
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $course = $this->post('/admin/education/academic/courses', [
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $course['code']);

        $teacher = EducationTeacher::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'teacher_no' => 'T001', 'name' => 'Teacher Wang', 'status' => 'enabled']);
        $this->put('/admin/education/academic/courses/' . $course['data']['id'] . '/teachers', ['teacher_ids' => [$teacher->id]], $this->tenantHeaders($tenant));

        $package = $this->post('/admin/education/academic/lesson-packages', [
            'campus_id' => $campus->id,
            'course_id' => $course['data']['id'],
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => 20,
            'bonus_units' => 4,
            'list_price' => 3600,
            'sale_price' => 3000,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student Zhang', 'status' => 'enabled']);
        $enrollment = $this->post('/admin/education/academic/enrollments', [
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course['data']['id'],
            'lesson_package_id' => $package['data']['id'],
        ], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/enrollments/' . $enrollment['data']['enrollment']['id'] . '/cancel', ['cancel_reason' => 'Audit'], $this->tenantHeaders($tenant));
        $this->put('/admin/education/academic/student-course-accounts/' . $enrollment['data']['account']['id'] . '/status', ['status' => 'frozen'], $this->tenantHeaders($tenant));

        foreach ([
            'education.academic.course.created',
            'education.academic.course_teacher.saved',
            'education.academic.lesson_package.created',
            'education.academic.enrollment.created',
            'education.academic.enrollment.cancelled',
            'education.academic.student_course_account.status_changed',
        ] as $action) {
            self::assertTrue(EducationAuditLog::query()->where('action', $action)->exists(), "missing audit {$action}");
        }
    }
}

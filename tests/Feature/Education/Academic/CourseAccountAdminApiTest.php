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
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationTeacher;

/**
 * @internal
 * @coversNothing
 */
final class CourseAccountAdminApiTest extends ProfileRecordAdminCase
{
    public function testCourseCrudReturnsMineadminShape(): void
    {
        $this->grantPermissions(
            'education:academic:course:page',
            'education:academic:course:create',
            'education:academic:course:update',
            'education:academic:course:status'
        );
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $create = $this->post('/admin/education/academic/courses', [
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertSame('ART-001', $create['data']['code']);

        $update = $this->put('/admin/education/academic/courses/' . $create['data']['id'], [
            'campus_id' => $campus->id,
            'code' => 'ART-002',
            'name' => 'Art Basics Updated',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $update['code']);
        self::assertSame('ART-002', $update['data']['code']);

        $status = $this->put('/admin/education/academic/courses/' . $create['data']['id'] . '/status', ['status' => 'disabled'], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $status['code']);
        self::assertSame('disabled', $status['data']['status']);

        $page = $this->get('/admin/education/academic/courses/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $campus->id,
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
    }

    public function testCourseTeacherSaveReturnsTeacherIds(): void
    {
        $this->grantPermissions('education:academic:course-teacher:page', 'education:academic:course-teacher:save');
        [$tenant, $campus] = $this->tenantCampusProfile();
        $course = $this->course((int) $tenant->id, (int) $campus->id);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher Wang',
            'status' => 'enabled',
        ]);

        $save = $this->put('/admin/education/academic/courses/' . $course->id . '/teachers', [
            'teacher_ids' => [$teacher->id],
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $save['code']);
        self::assertSame([(int) $teacher->id], $save['data']['teacher_ids']);

        $list = $this->get('/admin/education/academic/courses/' . $course->id . '/teachers', ['token' => $this->token], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame('Teacher Wang', $list['data']['list'][0]['teacher_name']);
    }

    public function testLessonPackageCrudReturnsTotalUnits(): void
    {
        $this->grantPermissions('education:academic:lesson-package:create', 'education:academic:lesson-package:update', 'education:academic:lesson-package:status', 'education:academic:lesson-package:page');
        [$tenant, $campus] = $this->tenantCampusProfile();
        $course = $this->course((int) $tenant->id, (int) $campus->id);

        $create = $this->post('/admin/education/academic/lesson-packages', [
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => 20,
            'bonus_units' => 4,
            'list_price' => 3600,
            'sale_price' => 3000,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $create['code']);
        self::assertSame('24.00', $create['data']['total_units']);
    }

    public function testEnrollmentCreateCancelAndLedgerWorkflow(): void
    {
        $this->grantPermissions(
            'education:academic:enrollment:create',
            'education:academic:enrollment:cancel',
            'education:academic:student-course-account:page',
            'education:academic:student-course-account:ledger'
        );
        [$tenant, $campus] = $this->tenantCampusProfile();
        [$student, $course, $package] = $this->studentCoursePackage((int) $tenant->id, (int) $campus->id);

        $created = $this->post('/admin/education/academic/enrollments', [
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('confirmed', $created['data']['enrollment']['status']);
        self::assertSame('24.00', $created['data']['account']['available_units']);

        $cancelled = $this->put('/admin/education/academic/enrollments/' . $created['data']['enrollment']['id'] . '/cancel', [
            'cancel_reason' => 'Wrong package',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::SUCCESS->value, $cancelled['code']);
        self::assertSame('cancelled', $cancelled['data']['enrollment']['status']);

        $accounts = $this->get('/admin/education/academic/student-course-accounts/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $campus->id,
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(1, $accounts['data']['total']);

        $ledger = $this->get('/admin/education/academic/student-course-accounts/' . $created['data']['account']['id'] . '/ledger', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
        ], ['X-Tenant-Id' => (string) $tenant->id]);
        self::assertSame(['enrollment_cancel', 'enrollment'], array_column($ledger['data']['list'], 'source_type'));
    }

    public function testValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:academic:course:create', 'education:academic:lesson-package:create');
        [$tenant, $campus] = $this->tenantCampusProfile();

        $validation = $this->post('/admin/education/academic/courses', [
            'campus_id' => $campus->id,
            'name' => 'No Code',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);

        $this->course((int) $tenant->id, (int) $campus->id);
        $duplicate = $this->post('/admin/education/academic/courses', [
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Duplicate',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
    }

    private function tenantCampusProfile(): array
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        return [$tenant, $campus];
    }

    private function course(int $tenantId, int $campusId): EducationCourse
    {
        return EducationCourse::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
    }

    private function studentCoursePackage(int $tenantId, int $campusId): array
    {
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_no' => 'S001',
            'name' => 'Student Zhang',
            'status' => 'enabled',
        ]);
        $course = $this->course($tenantId, $campusId);
        $package = EducationLessonPackage::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'status' => 'enabled',
        ]);

        return [$student, $course, $package];
    }
}

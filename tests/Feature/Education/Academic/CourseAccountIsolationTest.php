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

/**
 * @internal
 * @coversNothing
 */
final class CourseAccountIsolationTest extends ProfileRecordAdminCase
{
    public function testTenantUserCannotReadOtherTenantCourse(): void
    {
        $this->grantPermissions('education:academic:course:page');
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $this->createTenantProfile($tenantA);
        EducationCourse::query()->create(['tenant_id' => $tenantA->id, 'campus_id' => $campusA->id, 'code' => 'A001', 'name' => 'Course A', 'status' => 'enabled']);
        EducationCourse::query()->create(['tenant_id' => $tenantB->id, 'campus_id' => $campusB->id, 'code' => 'B001', 'name' => 'Course B', 'status' => 'enabled']);

        $page = $this->get('/admin/education/academic/courses/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $tenantA->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame('A001', $page['data']['list'][0]['code']);
    }

    public function testCampusScopedUserCannotEnrollOtherCampusStudent(): void
    {
        $this->grantPermissions('education:academic:enrollment:create');
        $tenant = $this->tenant();
        $allowedCampus = $this->campus($tenant, 'allowed');
        $blockedCampus = $this->campus($tenant, 'blocked');
        $this->createTenantProfile($tenant, 'academic_staff', $allowedCampus);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $blockedCampus->id, 'student_no' => 'S001', 'name' => 'Student', 'status' => 'enabled']);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $blockedCampus->id, 'code' => 'ART-001', 'name' => 'Art Basics', 'status' => 'enabled']);
        $package = EducationLessonPackage::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $blockedCampus->id,
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

        $result = $this->post('/admin/education/academic/enrollments', [
            'campus_id' => $blockedCampus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_package_id' => $package->id,
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

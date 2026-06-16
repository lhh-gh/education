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
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;

/**
 * @internal
 * @coversNothing
 */
final class ClassScheduleIsolationTest extends ProfileRecordAdminCase
{
    public function testTenantUserCannotReadOtherTenantClasses(): void
    {
        $this->grantPermissions('education:academic:class:page');
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $this->createTenantProfile($tenantA);
        $courseA = EducationCourse::query()->create(['tenant_id' => $tenantA->id, 'campus_id' => $campusA->id, 'code' => 'A001', 'name' => 'Course A', 'status' => 'enabled']);
        $courseB = EducationCourse::query()->create(['tenant_id' => $tenantB->id, 'campus_id' => $campusB->id, 'code' => 'B001', 'name' => 'Course B', 'status' => 'enabled']);
        EducationClass::query()->create(['tenant_id' => $tenantA->id, 'campus_id' => $campusA->id, 'course_id' => $courseA->id, 'code' => 'CA', 'name' => 'Class A', 'lesson_units' => '1.00', 'status' => 'enabled']);
        EducationClass::query()->create(['tenant_id' => $tenantB->id, 'campus_id' => $campusB->id, 'course_id' => $courseB->id, 'code' => 'CB', 'name' => 'Class B', 'lesson_units' => '1.00', 'status' => 'enabled']);

        $page = $this->get('/admin/education/academic/classes/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $tenantA->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame('CA', $page['data']['list'][0]['code']);
    }
}

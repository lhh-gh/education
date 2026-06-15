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

/**
 * @internal
 * @coversNothing
 */
final class CourseAccountPermissionTest extends ProfileRecordAdminCase
{
    public function testMissingCourseCreatePermissionReturns403(): void
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $result = $this->post('/admin/education/academic/courses', [
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'unit_minutes' => 60,
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testFrontDeskCannotCancelEnrollmentWithoutPermission(): void
    {
        $tenant = $this->tenant();
        $this->createTenantProfile($tenant, 'front_desk');

        $result = $this->put('/admin/education/academic/enrollments/1/cancel', [
            'cancel_reason' => 'No permission',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

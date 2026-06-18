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
final class ProfileRecordPermissionTest extends ProfileRecordAdminCase
{
    public function testStudentPageRequiresPermission(): void
    {
        $tenant = $this->tenant();
        $this->createTenantProfile($tenant);

        $result = $this->get('/admin/education/academic/students/page', ['token' => $this->token], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testTeacherCreateRequiresPermission(): void
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $result = $this->post('/admin/education/academic/teachers', [
            'campus_id' => $campus->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher Wang',
            'gender' => 'male',
            'status' => 'enabled',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

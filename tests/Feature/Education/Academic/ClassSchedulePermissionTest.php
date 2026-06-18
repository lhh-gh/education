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
final class ClassSchedulePermissionTest extends ProfileRecordAdminCase
{
    public function testMissingClassCreatePermissionReturns403(): void
    {
        $tenant = $this->tenant();
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant);

        $result = $this->post('/admin/education/academic/classes', [
            'campus_id' => $campus->id,
            'course_id' => 1,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'lesson_units' => 1,
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

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
final class GuardianMobileRoleIsolationTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testTeacherRoleRejected(): void
    {
        $tenant = $this->teacherProfileFixture('guardian_mobile_role_isolation');

        $result = $this->get('/mobile/education/academic/guardian/students', [], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('guardian mobile role required', $result['message']);
    }
}

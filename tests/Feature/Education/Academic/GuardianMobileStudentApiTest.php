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
final class GuardianMobileStudentApiTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testStudentsContract(): void
    {
        $fixture = $this->guardianFixture('guardian_mobile_student_api');

        $result = $this->get('/mobile/education/academic/guardian/students', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code'], json_encode($result, \JSON_UNESCAPED_UNICODE));
        self::assertSame(1, $result['data']['total']);
        self::assertSame((int) $fixture['student']->id, $result['data']['list'][0]['id']);
        self::assertTrue($result['data']['list'][0]['can_receive_notice']);
    }
}

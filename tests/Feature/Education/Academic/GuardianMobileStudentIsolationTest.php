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

/**
 * @internal
 * @coversNothing
 */
final class GuardianMobileStudentIsolationTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testUnboundStudentRejected(): void
    {
        $fixture = $this->guardianFixture('guardian_mobile_student_isolation');
        $other = EducationStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_no' => 'S-OTHER', 'name' => 'Other Student', 'status' => 'enabled']);

        $result = $this->get('/mobile/education/academic/guardian/students/' . $other->id . '/accounts', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('student is not bound to current guardian', $result['message']);
    }
}

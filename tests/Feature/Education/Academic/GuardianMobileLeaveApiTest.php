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
final class GuardianMobileLeaveApiTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testLeaveCreateSuccessAndDuplicateConflict(): void
    {
        $fixture = $this->guardianFixture('guardian_mobile_leave_api');
        $payload = ['lesson_student_id' => $fixture['lesson_student']->id, 'leave_type' => 'sick', 'reason' => 'Fever', 'makeup_required' => true];
        $headers = $this->mobileHeaders($fixture['tenant']);

        $created = $this->post('/mobile/education/academic/guardian/leave-requests', $payload, $headers);
        $duplicate = $this->post('/mobile/education/academic/guardian/leave-requests', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('guardian', $created['data']['source']);
        self::assertSame('pending', $created['data']['status']);
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
    }
}

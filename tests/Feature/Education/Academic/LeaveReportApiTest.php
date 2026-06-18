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
final class LeaveReportApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testLeaveReportContract(): void
    {
        $this->grantPermissions('education:academic:report:leave');
        $fixture = $this->reportFixture('leave_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/leaves', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
            'source' => 'guardian',
            'leave_type' => 'sick',
            'status' => 'pending',
        ]), $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['summary']['total_count']);
        self::assertSame(1, $response['data']['summary']['pending_count']);
        self::assertSame(1, $response['data']['summary']['guardian_source_count']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame('LEA-LEAVE_API', $response['data']['list'][0]['leave_no']);
    }
}

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
final class AccountBalanceReportApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testAccountBalanceReportContract(): void
    {
        $this->grantPermissions('education:academic:report:account-balance');
        $fixture = $this->reportFixture('balance_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/account-balances', [
            'page' => 1,
            'pageSize' => 20,
            'campus_id' => $fixture['campus']->id,
            'status' => 'active',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['summary']['account_count']);
        self::assertSame(1, $response['data']['summary']['active_count']);
        self::assertSame('20.00', $response['data']['summary']['total_purchased_units']);
        self::assertSame('19.00', $response['data']['summary']['total_available_units']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame('normal', $response['data']['list'][0]['balance_level']);
    }
}

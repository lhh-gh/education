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
final class V1AcceptanceSummaryApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testAcceptanceSummaryPassContract(): void
    {
        $this->grantPermissions('education:academic:report:acceptance');
        $fixture = $this->reportFixture('acceptance_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/v1-acceptance-summary', [
            'campus_id' => $fixture['campus']->id,
            'include_detail' => true,
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame('pass', $response['data']['overall_status']);
        self::assertSame(1, $response['data']['ledger']['account_count']);
        self::assertSame(0, $response['data']['ledger']['mismatch_count']);
        self::assertNotEmpty($response['data']['gates']);
        self::assertSame('V1 implementation is ready for product UAT', $response['data']['next_action']);
    }
}

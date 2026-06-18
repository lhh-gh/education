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
final class ConsumptionReportApiTest extends ProfileRecordAdminCase
{
    use AcademicReportApiFixture;

    public function testConsumptionReportContract(): void
    {
        $this->grantPermissions('education:academic:report:consumption');
        $fixture = $this->reportFixture('consumption_api');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/consumption', $this->reportRangeParams([
            'campus_id' => $fixture['campus']->id,
            'source_type' => 'attendance',
            'status' => 'active',
            'group_by' => 'course',
        ]), $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame('1.00', $response['data']['summary']['decrease_units']);
        self::assertSame('0.00', $response['data']['summary']['rollback_units']);
        self::assertSame('1.00', $response['data']['summary']['net_units']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame('CON-CONSUMPTION_API', $response['data']['list'][0]['consumption_no']);
    }

    public function testConsumptionDateRangeValidation(): void
    {
        $this->grantPermissions('education:academic:report:consumption');
        $fixture = $this->reportFixture('consumption_validation');
        $this->createTenantProfile($fixture['tenant']);

        $response = $this->get('/admin/education/academic/reports/consumption', $this->reportRangeParams([
            'start_at' => '2026-06-30 23:59:59',
            'end_at' => '2026-06-01 00:00:00',
        ]), $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $response['code']);
    }
}

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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\Education\Academic\AcademicReportService;

/**
 * @internal
 * @coversNothing
 */
final class AcademicReportServiceTest extends AcademicTestCase
{
    public function testDateRangeOver366DaysIsRejected(): void
    {
        $tenant = $this->tenant('report_service_range');

        try {
            make(AcademicReportService::class)->attendance([
                'page' => 1,
                'pageSize' => 20,
                'start_at' => '2026-01-01 00:00:00',
                'end_at' => '2027-01-03 00:00:00',
            ], $this->context((int) $tenant->id));
            self::fail('Expected overlong date range to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
            self::assertSame(['max_days' => 366], $exception->getResponse()->data);
        }
    }

    public function testEmptyReportReturnsZeroSummaryAndEmptyList(): void
    {
        $tenant = $this->tenant('report_service_empty');

        $report = make(AcademicReportService::class)->consumption([
            'page' => 1,
            'pageSize' => 20,
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
        ], $this->context((int) $tenant->id));

        self::assertSame('0.00', $report['summary']['net_units']);
        self::assertSame(0, $report['total']);
        self::assertSame([], $report['list']);
    }
}

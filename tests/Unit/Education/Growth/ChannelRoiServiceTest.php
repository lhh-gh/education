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

namespace HyperfTests\Unit\Education\Growth;

use App\Model\Education\Admissions\EducationLeadConversionRecord;
use App\Model\Education\Admissions\EducationLeadSource;
use App\Model\Education\Growth\EducationGrowthChannelCost;
use App\Service\Education\Growth\ChannelRoiService;

/**
 * @internal
 * @coversNothing
 */
final class ChannelRoiServiceTest extends GrowthTestCase
{
    public function testRoiAggregatesCostAndConvertedRevenue(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_roi');
        $source = EducationLeadSource::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'SRC' . uniqid(),
            'name' => 'Search Ads',
            'channel_type' => 'ads',
            'status' => 'enabled',
        ]);
        $lead = $this->leadFixture($tenant, $campus, ['source_id' => $source->id, 'owner_user_id' => 205]);
        $this->leadFixture($tenant, $campus, ['source_id' => $source->id, 'owner_user_id' => 205]);
        EducationGrowthChannelCost::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'source_id' => $source->id,
            'cost_date' => '2026-06-10',
            'cost_type' => 'ads',
            'amount_cents' => 100000,
        ]);
        EducationLeadConversionRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'student_id' => 1,
            'guardian_id' => 1,
            'enrollment_id' => 1,
            'student_course_account_id' => 1,
            'status' => 'converted',
            'converted_by' => 205,
            'converted_at' => '2026-06-10 10:00:00',
            'payload_json' => ['converted_revenue_cents' => 500000],
        ]);

        $result = make(ChannelRoiService::class)->aggregateDaily((int) $tenant->id, (int) $campus->id, '2026-06-10', (int) $source->id);

        self::assertSame(2, $result['lead_count']);
        self::assertSame(1, $result['converted_count']);
        self::assertSame(100000, $result['cost_cents']);
        self::assertSame(500000, $result['converted_revenue_cents']);
        self::assertSame('5.0000', $result['roi']);
    }
}

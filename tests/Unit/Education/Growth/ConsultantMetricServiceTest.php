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
use App\Model\Education\Admissions\EducationLeadFollowRecord;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Model\Education\Growth\EducationGrowthLeadLossRecord;
use App\Model\Education\Growth\EducationGrowthLossReason;
use App\Service\Education\Growth\ConsultantMetricService;

/**
 * @internal
 * @coversNothing
 */
final class ConsultantMetricServiceTest extends GrowthTestCase
{
    public function testConsultantMetricsAggregateDailyActivity(): void
    {
        [$tenant, $campus] = $this->tenantCampus('growth_consultant_metric');
        $lead = $this->leadFixture($tenant, $campus, ['owner_user_id' => 206]);
        EducationLeadFollowRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'follow_type' => 'phone',
            'content' => 'Followed',
            'result' => 'interested',
            'operator_user_id' => 206,
            'created_by' => 206,
            'updated_by' => 206,
            'created_at' => '2026-06-10 09:00:00',
        ]);
        EducationTrialLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'lead_student_id' => 1,
            'course_id' => 1,
            'teacher_id' => 306,
            'start_time' => '2026-06-10 10:00:00',
            'end_time' => '2026-06-10 11:00:00',
            'status' => 'completed',
            'consultant_user_id' => 206,
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
            'converted_by' => 206,
            'converted_at' => '2026-06-10 12:00:00',
            'payload_json' => [],
        ]);
        $reason = EducationGrowthLossReason::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'reason_code' => 'PRICE',
            'reason_name' => 'Price',
            'reason_group' => 'price',
            'status' => 'enabled',
        ]);
        EducationGrowthLeadLossRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'loss_reason_id' => $reason->id,
            'lost_by' => 206,
            'lost_at' => '2026-06-10 13:00:00',
        ]);

        $result = make(ConsultantMetricService::class)->aggregateDaily((int) $tenant->id, (int) $campus->id, 206, '2026-06-10');

        self::assertSame(1, $result['assigned_leads_count']);
        self::assertSame(1, $result['follow_count']);
        self::assertSame(1, $result['trial_count']);
        self::assertSame(1, $result['converted_count']);
        self::assertSame(1, $result['lost_count']);
    }
}

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

namespace HyperfTests\Feature\Education\Family;

use App\Http\Common\ResultCode;
use App\Model\Education\Family\EducationFamilyReadReceipt;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Family\LearningReportService;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class GuardianFamilyMobileApiTest extends FamilyApiCase
{
    public function testGuardianLearningReportsArePublishedBoundStudentOnlyAndReadReceiptIsIdempotent(): void
    {
        $fixture = $this->familyFixture('guardian_family_api', 'guardian');
        $service = make(LearningReportService::class);
        $adminContext = new EducationUserContext(7301, $fixture['tenant_id'], EducationRoleCode::TenantAdmin, false, [$fixture['campus_id']], $fixture['campus_id']);
        $published = $service->save([
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'report_title' => 'Published report',
            'report_period' => '2026-06',
            'summary' => 'Visible',
            'items' => [['item_type' => 'summary', 'title' => 'Summary', 'content' => 'Visible']],
        ], $adminContext);
        $withdrawn = $service->save([
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'report_title' => 'Withdrawn report',
            'report_period' => '2026-06',
            'summary' => 'Hidden',
            'items' => [['item_type' => 'summary', 'title' => 'Summary', 'content' => 'Hidden']],
        ], $adminContext);
        $service->publish($published['learning_report_id'], $adminContext);
        $service->publish($withdrawn['learning_report_id'], $adminContext);
        $service->withdraw($withdrawn['learning_report_id'], $adminContext);

        $first = $this->get('/mobile/education/family/guardian/students/' . $fixture['student_id'] . '/learning-reports', [], $this->mobileHeaders($fixture['tenant']));
        $second = $this->get('/mobile/education/family/guardian/students/' . $fixture['student_id'] . '/learning-reports', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $first['code']);
        self::assertSame(1, $first['data']['total']);
        self::assertSame('Published report', $first['data']['list'][0]['report_title']);
        self::assertSame(ResultCode::SUCCESS->value, $second['code']);
        self::assertSame(1, EducationFamilyReadReceipt::query()->where('business_type', 'learning_report')->count());
    }
}

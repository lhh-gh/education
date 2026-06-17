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

namespace HyperfTests\Unit\Education\Family;

use App\Model\Education\Family\EducationFamilyReadReceipt;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Family\LearningReportService;

/**
 * @internal
 * @coversNothing
 */
final class LearningReportServiceTest extends FamilyTestCase
{
    public function testDraftAndWithdrawnReportsAreHiddenFromGuardian(): void
    {
        $fixture = $this->familyFixture('family_report');
        $service = make(LearningReportService::class);
        $adminContext = $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']], userId: 7201);
        $guardianContext = $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [$fixture['campus_id']], $fixture['guardian_user_id']);

        $service->save([
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'report_title' => 'Draft report',
            'report_period' => '2026-06',
            'summary' => 'Hidden draft',
            'items' => [['item_type' => 'summary', 'title' => 'Summary', 'content' => 'Draft']],
        ], $adminContext);
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
            'summary' => 'Hidden withdrawn',
            'items' => [['item_type' => 'summary', 'title' => 'Summary', 'content' => 'Withdrawn']],
        ], $adminContext);
        $service->publish($published['learning_report_id'], $adminContext);
        $service->publish($withdrawn['learning_report_id'], $adminContext);
        $service->withdraw($withdrawn['learning_report_id'], $adminContext);

        $firstRead = $service->visibleForGuardian($fixture['student_id'], [], $guardianContext);
        $secondRead = $service->visibleForGuardian($fixture['student_id'], [], $guardianContext);

        self::assertSame(1, $firstRead['total']);
        self::assertSame('Published report', $firstRead['list'][0]['report_title']);
        self::assertSame(1, $secondRead['total']);
        self::assertSame(1, EducationFamilyReadReceipt::query()->where('business_type', 'learning_report')->count());
    }
}

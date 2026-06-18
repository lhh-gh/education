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

/**
 * @internal
 * @coversNothing
 */
final class FamilyAdminApiTest extends FamilyApiCase
{
    public function testHomeworkValidationAndReportPublishConflictMatchCatalog(): void
    {
        $fixture = $this->familyFixture('family_admin_api', 'tenant_admin');
        $this->grantPermissions('education:family:homework:create', 'education:family:report:create', 'education:family:report:publish');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalid = $this->post('/admin/education/family/homework-assignments', [
            'content' => 'Missing title',
            'student_ids' => [$fixture['student_id']],
        ], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);
        self::assertSame('title is required', $invalid['message']);

        $homework = $this->post('/admin/education/family/homework-assignments', [
            'title' => 'Unit 1 practice',
            'content' => 'Finish worksheet',
            'class_id' => $fixture['class_id'],
            'lesson_id' => $fixture['lesson_id'],
            'student_ids' => [$fixture['student_id']],
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $homework['code']);
        self::assertSame(1, $homework['data']['target_count']);

        $report = $this->post('/admin/education/family/learning-reports', [
            'student_id' => $fixture['student_id'],
            'report_title' => 'Empty report',
            'report_period' => '2026-06',
            'items' => [],
        ], $headers);
        $publish = $this->post('/admin/education/family/learning-reports/' . $report['data']['learning_report_id'] . '/publish', [], $headers);

        self::assertSame(ResultCode::CONFLICT->value, $publish['code']);
        self::assertSame('learning report has no report items', $publish['message']);
    }
}

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

namespace HyperfTests\Unit\Education\Ai;

use App\Exception\BusinessException;
use App\Model\Education\Ai\EducationAiDataQuestionLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\DataQuestionService;

/**
 * @internal
 * @coversNothing
 */
final class DataQuestionServiceTest extends AiTestCase
{
    public function testDataQuestionUsesMetricCatalogNotRawSql(): void
    {
        $fixture = $this->aiFixture('ai_data_question');
        $service = make(DataQuestionService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::TenantAdmin, [$fixture['campus_id']], 7001);

        $answer = $service->ask([
            'question_text' => 'How many renewal alerts this month?',
            'metric_codes' => ['renewal_alert_count'],
        ], $context);

        self::assertSame('succeeded', $answer['status']);
        self::assertTrue(EducationAiDataQuestionLog::query()->where('id', $answer['question_log_id'])->exists());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);
        $service->ask([
            'question_text' => 'select * from edu_students',
            'metric_codes' => ['renewal_alert_count'],
        ], $context);
    }
}

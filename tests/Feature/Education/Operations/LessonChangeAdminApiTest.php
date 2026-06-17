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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class LessonChangeAdminApiTest extends OperationApiCase
{
    public function testCreateApproveApplyLessonChange(): void
    {
        $this->grantPermissions('education:operations:lesson-change:create', 'education:operations:lesson-change:approve', 'education:operations:lesson-change:apply');
        $fixture = $this->fixture('ops_lesson_api');
        $this->createTenantProfile($fixture['tenant']);
        $created = $this->post('/admin/education/operations/lesson-change-requests', ['lesson_id' => $fixture['lesson']->id, 'change_type' => 'reschedule', 'new_values_json' => ['start_time' => '2026-06-10 14:00:00', 'end_time' => '2026-06-10 15:00:00'], 'reason' => 'training'], $this->tenantHeaders($fixture['tenant']));
        $approved = $this->post('/admin/education/operations/lesson-change-requests/' . $created['data']['id'] . '/approve', [], $this->tenantHeaders($fixture['tenant']));
        $applied = $this->post('/admin/education/operations/lesson-change-requests/' . $created['data']['id'] . '/apply', [], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('approved', $approved['data']['status']);
        self::assertSame('applied', $applied['data']['status']);
    }

    public function testBatchChangeReturnsSuccessAndFailedLists(): void
    {
        $this->grantPermissions('education:operations:lesson-change:batch');
        $fixture = $this->fixture('ops_lesson_batch');
        $this->createTenantProfile($fixture['tenant']);
        $result = $this->post('/admin/education/operations/lessons/batch-change', ['lesson_ids' => [$fixture['lesson']->id, 999999], 'change_type' => 'cancel', 'reason' => 'holiday'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame([(int) $fixture['lesson']->id], $result['data']['success_ids']);
        self::assertNotEmpty($result['data']['failed']);
    }
}

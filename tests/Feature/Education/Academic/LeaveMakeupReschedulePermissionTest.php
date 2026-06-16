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
final class LeaveMakeupReschedulePermissionTest extends LeaveMakeupRescheduleTestCase
{
    public function testMissingLeaveApprovePermissionReturns403(): void
    {
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $leave = $this->createLeave($fixture);

        $result = $this->put('/admin/education/academic/leave-requests/' . $leave->id . '/approve', ['review_remark' => 'ok'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testFrontDeskCannotRescheduleLesson(): void
    {
        $this->grantPermissions('education:academic:lesson-change:reschedule');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant'], 'front_desk', $fixture['campus']);

        $result = $this->post('/admin/education/academic/lesson-changes/reschedule', [
            'source_lesson_id' => $fixture['lesson']->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Drawing Rescheduled',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'reschedule',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

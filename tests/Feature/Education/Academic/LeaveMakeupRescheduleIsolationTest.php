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
final class LeaveMakeupRescheduleIsolationTest extends LeaveMakeupRescheduleTestCase
{
    public function testTenantUserCannotReadOtherTenantLeave(): void
    {
        $this->grantPermissions('education:academic:leave-request:page', 'education:academic:leave-request:detail');
        $tenantA = $this->fixture();
        $tenantB = $this->fixture();
        $leaveA = $this->createLeave($tenantA);
        $leaveB = $this->createLeave($tenantB);
        $this->createTenantProfile($tenantA['tenant']);

        $page = $this->get('/admin/education/academic/leave-requests/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $tenantA['tenant']->id]);
        $detail = $this->get('/admin/education/academic/leave-requests/' . $leaveB->id, ['token' => $this->token], ['X-Tenant-Id' => (string) $tenantA['tenant']->id]);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame((int) $leaveA->id, (int) $page['data']['list'][0]['id']);
        self::assertSame(ResultCode::NOT_FOUND->value, $detail['code']);
    }

    public function testCampusScopedUserCannotMakeupOtherCampusLeave(): void
    {
        $this->grantPermissions('education:academic:lesson-change:makeup');
        $fixture = $this->fixture();
        $other = $this->fixture();
        $leave = $this->createLeave($other, 'approved');
        $this->createTenantProfile($fixture['tenant'], 'academic_staff', $fixture['campus']);

        $result = $this->post('/admin/education/academic/lesson-changes/makeup', [
            'leave_request_id' => $leave->id,
            'teacher_id' => $other['teacher']->id,
            'title' => 'Art Make-up',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'makeup',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
    }

    public function testRescheduleRespectsCampusScope(): void
    {
        $this->grantPermissions('education:academic:lesson-change:reschedule');
        $fixture = $this->fixture();
        $other = $this->fixture();
        $this->createTenantProfile($fixture['tenant'], 'academic_staff', $fixture['campus']);

        $result = $this->post('/admin/education/academic/lesson-changes/reschedule', [
            'source_lesson_id' => $other['lesson']->id,
            'teacher_id' => $other['teacher']->id,
            'title' => 'Drawing Rescheduled',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'reschedule',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
    }
}

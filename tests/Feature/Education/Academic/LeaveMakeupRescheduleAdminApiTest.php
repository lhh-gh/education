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
final class LeaveMakeupRescheduleAdminApiTest extends LeaveMakeupRescheduleTestCase
{
    public function testLeaveRequestCrudReviewReturnsMineadminShape(): void
    {
        $this->grantPermissions('education:academic:leave-request:page', 'education:academic:leave-request:detail', 'education:academic:leave-request:create', 'education:academic:leave-request:approve', 'education:academic:leave-request:reject', 'education:academic:leave-request:cancel');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $created = $this->post('/admin/education/academic/leave-requests', [
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
        ], $this->tenantHeaders($fixture['tenant']));
        $page = $this->get('/admin/education/academic/leave-requests/page', ['token' => $this->token, 'page' => 1, 'pageSize' => 20], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $detail = $this->get('/admin/education/academic/leave-requests/' . $created['data']['id'], ['token' => $this->token], ['X-Tenant-Id' => (string) $fixture['tenant']->id]);
        $approved = $this->put('/admin/education/academic/leave-requests/' . $created['data']['id'] . '/approve', ['review_remark' => 'ok'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame(1, $page['data']['total']);
        self::assertSame((int) $created['data']['id'], (int) $detail['data']['id']);
        self::assertSame('approved', $approved['data']['status']);
    }

    public function testMakeupReturnsLeaveLessonAndChangeSummary(): void
    {
        $this->grantPermissions('education:academic:lesson-change:makeup');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $leave = $this->createLeave($fixture, 'approved');

        $result = $this->post('/admin/education/academic/lesson-changes/makeup', [
            'leave_request_id' => $leave->id,
            'teacher_id' => $fixture['teacher']->id,
            'classroom_id' => $fixture['classroom']->id,
            'title' => 'Art Make-up',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'makeup',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame('makeup_scheduled', $result['data']['leave_request']['status']);
        self::assertSame('makeup', $result['data']['change_record']['change_type']);
        self::assertSame(1, (int) $result['data']['target_lesson']['student_count']);
    }

    public function testRescheduleReturnsUpdatedLessonAndChangeRecord(): void
    {
        $this->grantPermissions('education:academic:lesson-change:reschedule');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $result = $this->post('/admin/education/academic/lesson-changes/reschedule', [
            'source_lesson_id' => $fixture['lesson']->id,
            'teacher_id' => $fixture['teacher']->id,
            'classroom_id' => $fixture['classroom']->id,
            'title' => 'Drawing Rescheduled',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'reschedule',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame('Drawing Rescheduled', $result['data']['lesson']['title']);
        self::assertSame('reschedule', $result['data']['change_record']['change_type']);
    }

    public function testValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:academic:leave-request:create');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $validation = $this->post('/admin/education/academic/leave-requests', [], $this->tenantHeaders($fixture['tenant']));
        $first = $this->post('/admin/education/academic/leave-requests', [
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
        ], $this->tenantHeaders($fixture['tenant']));
        $duplicate = $this->post('/admin/education/academic/leave-requests', [
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
        ], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);
        self::assertSame(ResultCode::SUCCESS->value, $first['code']);
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
    }
}

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

use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class LeaveMakeupRescheduleAuditTest extends LeaveMakeupRescheduleTestCase
{
    public function testLeaveCreateReviewCancelCreateAuditLogs(): void
    {
        $this->grantPermissions('education:academic:leave-request:create', 'education:academic:leave-request:approve', 'education:academic:leave-request:cancel');
        $fixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);

        $created = $this->post('/admin/education/academic/leave-requests', [
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
        ], $this->tenantHeaders($fixture['tenant']));
        $this->put('/admin/education/academic/leave-requests/' . $created['data']['id'] . '/approve', ['review_remark' => 'ok'], $this->tenantHeaders($fixture['tenant']));

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.leave_request.created')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.leave_request.approved')->exists());
    }

    public function testMakeupAndRescheduleCreateAuditLogs(): void
    {
        $this->grantPermissions('education:academic:lesson-change:makeup', 'education:academic:lesson-change:reschedule');
        $fixture = $this->fixture();
        $rescheduleFixture = $this->fixture();
        $this->createTenantProfile($fixture['tenant']);
        $this->createTenantProfile($rescheduleFixture['tenant']);
        $leave = $this->createLeave($fixture, 'approved');
        $this->post('/admin/education/academic/lesson-changes/makeup', [
            'leave_request_id' => $leave->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Art Make-up',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'makeup',
        ], $this->tenantHeaders($fixture['tenant']));
        $this->post('/admin/education/academic/lesson-changes/reschedule', [
            'source_lesson_id' => $rescheduleFixture['lesson']->id,
            'teacher_id' => $rescheduleFixture['teacher']->id,
            'title' => 'Drawing Rescheduled',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'reschedule',
        ], $this->tenantHeaders($rescheduleFixture['tenant']));

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.lesson_change.makeup_created')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.lesson_change.rescheduled')->exists());
    }
}

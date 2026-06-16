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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Service\Education\Academic\LeaveRequestService;

/**
 * @internal
 * @coversNothing
 */
final class LeaveRequestServiceTest extends LeaveMakeupRescheduleTestCase
{
    public function testCreateLeaveRequestFromLessonStudentSnapshot(): void
    {
        $fixture = $this->fixture();

        $leave = make(LeaveRequestService::class)->create([
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
            'makeup_required' => true,
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('pending', $leave->status);
        self::assertSame((int) $fixture['lesson']->id, (int) $leave->lesson_id);
        self::assertSame((int) $fixture['lessonStudent']->student_id, (int) $leave->student_id);
        self::assertSame((int) $fixture['lessonStudent']->account_id, (int) $leave->account_id);
    }

    public function testCreateRejectsDuplicateLessonStudent(): void
    {
        $fixture = $this->fixture();
        $service = make(LeaveRequestService::class);
        $payload = [
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'source' => 'staff',
            'leave_type' => 'sick',
            'reason' => 'Sick leave',
        ];
        $service->create($payload, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        try {
            $service->create($payload, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected duplicate leave request to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testApproveRejectsWhenConsumptionExists(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'pending');
        EducationLessonConsumption::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'consumption_no' => 'CON001',
            'account_id' => $fixture['account']->id,
            'student_id' => $fixture['student']->id,
            'course_id' => $fixture['course']->id,
            'lesson_id' => $fixture['lesson']->id,
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'attendance_id' => 1,
            'source_type' => 'attendance',
            'direction' => 'decrease',
            'units' => '1.00',
            'before_available_units' => '10.00',
            'after_available_units' => '9.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => '1.00',
            'status' => 'active',
        ]);

        try {
            make(LeaveRequestService::class)->approve((int) $leave->id, 'ok', $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected consumed lesson student approval to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testApprovePendingLeaveSetsReviewFields(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'pending');

        $approved = make(LeaveRequestService::class)->approve((int) $leave->id, 'Approved before class', $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('approved', $approved->status);
        self::assertSame(901, (int) $approved->reviewed_by);
        self::assertSame('Approved before class', $approved->review_remark);
    }

    public function testRejectPendingLeaveSetsReviewFields(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'pending');

        $rejected = make(LeaveRequestService::class)->reject((int) $leave->id, 'No proof', $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('rejected', $rejected->status);
        self::assertSame(901, (int) $rejected->reviewed_by);
        self::assertSame('No proof', $rejected->review_remark);
    }

    public function testCancelMakeupScheduledLeaveIsRejected(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'makeup_scheduled');

        try {
            make(LeaveRequestService::class)->cancel((int) $leave->id, 'cancel', $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected makeup scheduled leave cancellation to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function leave(array $fixture, string $status): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'leave_no' => uniqid('LEA'),
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $fixture['lesson']->id,
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'class_id' => $fixture['class']->id,
            'course_id' => $fixture['course']->id,
            'student_id' => $fixture['student']->id,
            'account_id' => $fixture['account']->id,
            'teacher_id' => $fixture['teacher']->id,
            'reason' => 'Sick leave',
            'status' => $status,
            'makeup_required' => true,
        ]);
    }
}

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
use App\Model\Education\Academic\EducationLesson;
use App\Service\Education\Academic\MakeupLessonService;

/**
 * @internal
 * @coversNothing
 */
final class MakeupLessonServiceTest extends LeaveMakeupRescheduleTestCase
{
    public function testMakeupCreatesSingleStudentLessonAndChangeRecord(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'approved');

        $result = make(MakeupLessonService::class)->create([
            'leave_request_id' => $leave->id,
            'teacher_id' => $fixture['teacher']->id,
            'classroom_id' => $fixture['classroom']->id,
            'title' => 'Art Make-up',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'Make up sick leave',
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('makeup_scheduled', $result['leave_request']->status);
        self::assertSame(1, (int) $result['target_lesson']->student_count);
        self::assertSame('makeup', $result['change_record']->change_type);
        self::assertSame((int) $fixture['account']->id, (int) $result['target_lesson_student']->account_id);
    }

    public function testMakeupRejectsUnapprovedLeave(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'pending');

        try {
            make(MakeupLessonService::class)->create([
                'leave_request_id' => $leave->id,
                'teacher_id' => $fixture['teacher']->id,
                'title' => 'Art Make-up',
                'start_at' => '2026-06-17 09:00:00',
                'end_at' => '2026-06-17 10:00:00',
                'lesson_units' => '1.00',
                'reason' => 'Make up sick leave',
            ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected pending leave makeup to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testMakeupRejectsStudentConflict(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'approved');
        $this->conflictingLesson($fixture, '2026-06-17 09:30:00', '2026-06-17 10:30:00');

        try {
            make(MakeupLessonService::class)->create([
                'leave_request_id' => $leave->id,
                'teacher_id' => $fixture['teacher']->id,
                'title' => 'Art Make-up',
                'start_at' => '2026-06-17 09:00:00',
                'end_at' => '2026-06-17 10:00:00',
                'lesson_units' => '1.00',
                'reason' => 'Make up sick leave',
            ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected student conflict to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertTrue($exception->getResponse()->data['has_conflict']);
        }
    }

    public function testMakeupDoesNotChangeAccountBalance(): void
    {
        $fixture = $this->fixture();
        $leave = $this->leave($fixture, 'approved');

        make(MakeupLessonService::class)->create([
            'leave_request_id' => $leave->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Art Make-up',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'Make up sick leave',
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('10.00', $fixture['account']->refresh()->available_units);
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

    private function conflictingLesson(array $fixture, string $startAt, string $endAt): void
    {
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => uniqid('L'),
            'class_id' => $fixture['class']->id,
            'course_id' => $fixture['course']->id,
            'teacher_id' => 9999,
            'title' => 'Conflict',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Other',
        ]);
        $this->lessonStudentFor($fixture, (int) $lesson->id);
    }
}

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
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Service\Education\Academic\RescheduleService;

/**
 * @internal
 * @coversNothing
 */
final class RescheduleServiceTest extends LeaveMakeupRescheduleTestCase
{
    public function testRescheduleUpdatesLessonAndCreatesChangeRecord(): void
    {
        $fixture = $this->fixture();

        $result = make(RescheduleService::class)->reschedule([
            'source_lesson_id' => $fixture['lesson']->id,
            'teacher_id' => $fixture['teacher']->id,
            'classroom_id' => $fixture['classroom']->id,
            'title' => 'Drawing Rescheduled',
            'start_at' => '2026-06-17 09:00:00',
            'end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.50',
            'reason' => 'Teacher meeting',
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('Drawing Rescheduled', $result['lesson']->title);
        self::assertSame('reschedule', $result['change_record']->change_type);
        self::assertSame((int) $fixture['lesson']->id, (int) $result['change_record']->target_lesson_id);
    }

    public function testRescheduleRejectsCompletedLesson(): void
    {
        $fixture = $this->fixture('completed');

        try {
            make(RescheduleService::class)->reschedule([
                'source_lesson_id' => $fixture['lesson']->id,
                'teacher_id' => $fixture['teacher']->id,
                'title' => 'Drawing Rescheduled',
                'start_at' => '2026-06-17 09:00:00',
                'end_at' => '2026-06-17 10:00:00',
                'lesson_units' => '1.00',
                'reason' => 'Teacher meeting',
            ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected completed lesson reschedule to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testRescheduleRejectsLessonWithAttendance(): void
    {
        $fixture = $this->fixture();
        EducationLessonAttendance::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_id' => $fixture['lesson']->id,
            'lesson_student_id' => $fixture['lessonStudent']->id,
            'class_id' => $fixture['class']->id,
            'course_id' => $fixture['course']->id,
            'student_id' => $fixture['student']->id,
            'account_id' => $fixture['account']->id,
            'attendance_status' => 'present',
            'consume_policy' => 'no_consume',
            'planned_units' => '1.00',
            'consumed_units' => '0.00',
            'consumption_status' => 'none',
            'attendance_batch_no' => 'ATT001',
        ]);

        try {
            make(RescheduleService::class)->reschedule([
                'source_lesson_id' => $fixture['lesson']->id,
                'teacher_id' => $fixture['teacher']->id,
                'title' => 'Drawing Rescheduled',
                'start_at' => '2026-06-17 09:00:00',
                'end_at' => '2026-06-17 10:00:00',
                'lesson_units' => '1.00',
                'reason' => 'Teacher meeting',
            ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected attended lesson reschedule to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testRescheduleRejectsTeacherConflict(): void
    {
        $fixture = $this->fixture();
        EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => uniqid('L'),
            'class_id' => 9999,
            'course_id' => $fixture['course']->id,
            'teacher_id' => $fixture['teacher']->id,
            'title' => 'Conflict',
            'start_at' => '2026-06-17 09:30:00',
            'end_at' => '2026-06-17 10:30:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Other',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        try {
            make(RescheduleService::class)->reschedule([
                'source_lesson_id' => $fixture['lesson']->id,
                'teacher_id' => $fixture['teacher']->id,
                'title' => 'Drawing Rescheduled',
                'start_at' => '2026-06-17 09:00:00',
                'end_at' => '2026-06-17 10:00:00',
                'lesson_units' => '1.00',
                'reason' => 'Teacher meeting',
            ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected teacher conflict to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertTrue($exception->getResponse()->data['has_conflict']);
        }
    }
}

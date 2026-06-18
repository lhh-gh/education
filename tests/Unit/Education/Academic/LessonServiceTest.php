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
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\LessonService;

/**
 * @internal
 * @coversNothing
 */
final class LessonServiceTest extends AcademicTestCase
{
    public function testUpdateScheduledLessonRerunsConflictChecks(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();
        EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => 'L-CONFLICT',
            'class_id' => $lesson->class_id + 1,
            'course_id' => $lesson->course_id,
            'teacher_id' => 99,
            'title' => 'Conflict',
            'start_at' => '2026-06-15 11:00:00',
            'end_at' => '2026-06-15 12:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 0,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Other',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Other Teacher',
        ]);

        try {
            make(LessonService::class)->update((int) $lesson->id, [
                'teacher_id' => 99,
                'start_at' => '2026-06-15 11:30:00',
                'end_at' => '2026-06-15 12:30:00',
            ], $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected lesson update conflict to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testUpdateLessonUnitsUpdatesPlannedSnapshots(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();

        $updated = make(LessonService::class)->update((int) $lesson->id, [
            'lesson_units' => '1.50',
        ], $this->context($tenantId, campusIds: [$campusId]), 901);

        $snapshot = EducationLessonStudent::query()->where('lesson_id', $lesson->id)->first();
        self::assertSame('1.50', $updated->lesson_units);
        self::assertSame('1.50', $snapshot?->lesson_units);
    }

    public function testCancelCancelsSnapshotsWithoutChangingAccountBalance(): void
    {
        [$tenantId, $campusId, $lesson, $account] = $this->fixture();

        $cancelled = make(LessonService::class)->cancel((int) $lesson->id, 'Rain', $this->context($tenantId, campusIds: [$campusId]), 901);
        $account->refresh();

        self::assertSame('cancelled', $cancelled->status);
        self::assertSame(1, EducationLessonStudent::query()->where('lesson_id', $lesson->id)->where('status', 'cancelled')->count());
        self::assertSame('10.00', $account->available_units);
    }

    public function testDeleteCompletedLessonIsRejected(): void
    {
        [$tenantId, $campusId, $lesson] = $this->fixture();
        $lesson->status = 'completed';
        $lesson->save();

        try {
            make(LessonService::class)->delete((int) $lesson->id, $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected completed lesson delete to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 101,
            'course_id' => $course->id,
            'available_units' => '10.00',
            'status' => 'active',
        ]);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => 'L001',
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => 11,
            'title' => 'Lesson',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);
        EducationLessonStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => $lesson->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => 101,
            'account_id' => $account->id,
            'student_name_snapshot' => 'Student',
            'student_no_snapshot' => 'S001',
            'lesson_units' => '1.00',
            'status' => 'planned',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $lesson, $account];
    }
}

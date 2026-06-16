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
use App\Model\Education\Academic\EducationClassroom;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Service\Education\Academic\SchedulingService;

/**
 * @internal
 * @coversNothing
 */
final class SchedulingServiceTest extends AcademicTestCase
{
    public function testScheduleSingleCreatesLessonAndSnapshotsTransactionally(): void
    {
        [$tenantId, $campusId, $class, $teacher, $classroom, $student] = $this->fixture(withActiveStudent: true);

        $result = make(SchedulingService::class)->scheduleSingle([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'title' => 'Drawing',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:30:00',
            'lesson_units' => '1.50',
        ], $this->context($tenantId, campusIds: [$campusId]), 901);

        self::assertSame('Drawing', $result['lesson']->title);
        self::assertSame(90, (int) $result['lesson']->duration_minutes);
        self::assertSame(1, $result['lesson']->student_count);
        self::assertSame((int) $student->id, (int) $result['students'][0]['student_id']);
    }

    public function testScheduleSingleRejectsEmptyActiveStudents(): void
    {
        [$tenantId, $campusId, $class, $teacher, $classroom] = $this->fixture(withActiveStudent: false);

        try {
            make(SchedulingService::class)->scheduleSingle([
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id,
                'start_at' => '2026-06-15 09:00:00',
                'end_at' => '2026-06-15 10:00:00',
            ], $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected empty active students to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testScheduleBatchIsAllOrNothingOnConflict(): void
    {
        [$tenantId, $campusId, $class, $teacher, $classroom] = $this->fixture(withActiveStudent: true);
        EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => 'L001',
            'class_id' => $class->id,
            'course_id' => $class->course_id,
            'teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'title' => 'Existing',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 0,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher',
        ]);

        try {
            make(SchedulingService::class)->scheduleBatch([
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-17',
                'weekdays' => [1, 3],
                'start_time' => '09:00',
                'end_time' => '10:00',
            ], $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected batch conflict to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame(1, EducationLesson::query()->count());
        }
    }

    public function testGenerateBatchLessonPayloadsCountsWeekdays(): void
    {
        $rows = make(SchedulingService::class)->generateBatchLessonPayloads([
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-21',
            'weekdays' => [1, 3],
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        self::assertCount(2, $rows);
        self::assertSame('2026-06-15 09:00:00', $rows[0]['start_at']);
        self::assertSame('2026-06-17 09:00:00', $rows[1]['start_at']);
    }

    private function fixture(bool $withActiveStudent): array
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
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher',
            'status' => 'enabled',
        ]);
        EducationTeacherCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'status' => 'enabled',
        ]);
        $classroom = EducationClassroom::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'R001',
            'name' => 'Room 1',
            'capacity' => 20,
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'main_teacher_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student',
            'status' => 'enabled',
        ]);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'available_units' => '10.00',
            'status' => 'active',
        ]);
        if ($withActiveStudent) {
            EducationClassStudent::query()->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'class_id' => $class->id,
                'course_id' => $course->id,
                'student_id' => $student->id,
                'account_id' => $account->id,
                'student_name_snapshot' => $student->name,
                'student_no_snapshot' => $student->student_no,
                'status' => 'active',
            ]);
        }

        return [(int) $tenant->id, (int) $campus->id, $class, $teacher, $classroom, $student];
    }
}

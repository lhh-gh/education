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
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Academic\EducationTeacherCourse;
use App\Service\Education\Academic\ClassService;

/**
 * @internal
 * @coversNothing
 */
final class ClassServiceTest extends AcademicTestCase
{
    public function testCreateRejectsDuplicateClassCode(): void
    {
        [$tenantId, $campusId, $course, $teacher] = $this->fixture(authorizeTeacher: true);
        EducationClass::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $course->id,
            'code' => 'C-001',
            'name' => 'Existing Class',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);

        try {
            make(ClassService::class)->create($this->payload($campusId, (int) $course->id, (int) $teacher->id), $this->context($tenantId), 901);
            self::fail('Expected duplicate class code to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('C-001', $exception->getResponse()->data['code']);
        }
    }

    public function testCreateRejectsTeacherWithoutCourseAuthorization(): void
    {
        [$tenantId, $campusId, $course, $teacher] = $this->fixture(authorizeTeacher: false);

        try {
            make(ClassService::class)->create($this->payload($campusId, (int) $course->id, (int) $teacher->id), $this->context($tenantId), 901);
            self::fail('Expected unauthorized teacher to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
            self::assertSame((int) $teacher->id, $exception->getResponse()->data['teacher_id']);
        }
    }

    public function testDeleteRejectsClassWithLessonReference(): void
    {
        [$tenantId, $campusId, $course, $teacher] = $this->fixture(authorizeTeacher: true);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $course->id,
            'main_teacher_id' => $teacher->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => 'L001',
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Lesson',
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
            make(ClassService::class)->delete((int) $class->id, $this->context($tenantId), 901);
            self::fail('Expected referenced class delete to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(bool $authorizeTeacher): array
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
        if ($authorizeTeacher) {
            EducationTeacherCourse::query()->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'status' => 'enabled',
            ]);
        }

        return [(int) $tenant->id, (int) $campus->id, $course, $teacher];
    }

    private function payload(int $campusId, int $courseId, int $teacherId): array
    {
        return [
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'main_teacher_id' => $teacherId,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => '1.00',
        ];
    }
}

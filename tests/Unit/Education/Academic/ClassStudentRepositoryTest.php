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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\Education\Academic\ClassStudentRepository;

/**
 * @internal
 * @coversNothing
 */
final class ClassStudentRepositoryTest extends AcademicTestCase
{
    public function testReplaceStudentsMarksRemovedStudentsLeft(): void
    {
        [$tenantId, $campusId, $class, $studentA, $studentB, $accountA, $accountB] = $this->fixture();
        EducationClassStudent::query()->create($this->studentRow($tenantId, $campusId, $class, $studentA, $accountA));
        EducationClassStudent::query()->create($this->studentRow($tenantId, $campusId, $class, $studentB, $accountB));

        make(ClassStudentRepository::class)->replaceStudents((int) $class->id, [
            $this->studentRow($tenantId, $campusId, $class, $studentA, $accountA),
        ], $tenantId, $campusId, 901);

        $activeIds = make(ClassStudentRepository::class)->studentIdsByClass((int) $class->id, $tenantId);
        $left = EducationClassStudent::query()->where('student_id', $studentB->id)->first();

        self::assertSame([(int) $studentA->id], $activeIds);
        self::assertSame('left', $left?->status);
        self::assertNotNull($left?->left_at);
        self::assertSame(901, (int) $left?->updated_by);
    }

    public function testActiveStudentsByClassExcludesPausedAndLeft(): void
    {
        [$tenantId, $campusId, $class, $studentA, $studentB, $accountA, $accountB] = $this->fixture();
        $active = EducationClassStudent::query()->create($this->studentRow($tenantId, $campusId, $class, $studentA, $accountA));
        EducationClassStudent::query()->create([...$this->studentRow($tenantId, $campusId, $class, $studentB, $accountB), 'status' => 'paused']);
        EducationClassStudent::query()->create([...$this->studentRow($tenantId, $campusId, $class, $this->student($tenantId, $campusId, 'S003'), $accountB), 'student_id' => 9999, 'status' => 'left']);

        $rows = make(ClassStudentRepository::class)->activeStudentsByClass((int) $class->id, $tenantId, $campusId);

        self::assertCount(1, $rows);
        self::assertSame((int) $active->student_id, (int) $rows[0]['student_id']);
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
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $studentA = $this->student((int) $tenant->id, (int) $campus->id, 'S001');
        $studentB = $this->student((int) $tenant->id, (int) $campus->id, 'S002');
        $accountA = $this->account((int) $tenant->id, (int) $campus->id, (int) $studentA->id, (int) $course->id);
        $accountB = $this->account((int) $tenant->id, (int) $campus->id, (int) $studentB->id, (int) $course->id);

        return [(int) $tenant->id, (int) $campus->id, $class, $studentA, $studentB, $accountA, $accountB];
    }

    private function student(int $tenantId, int $campusId, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_no' => $studentNo,
            'name' => 'Student ' . $studentNo,
            'status' => 'enabled',
        ]);
    }

    private function account(int $tenantId, int $campusId, int $studentId, int $courseId): EducationStudentCourseAccount
    {
        return EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'available_units' => '10.00',
            'status' => 'active',
        ]);
    }

    private function studentRow(int $tenantId, int $campusId, EducationClass $class, EducationStudent $student, EducationStudentCourseAccount $account): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'class_id' => (int) $class->id,
            'course_id' => (int) $class->course_id,
            'student_id' => (int) $student->id,
            'account_id' => (int) $account->id,
            'student_name_snapshot' => $student->name,
            'student_no_snapshot' => $student->student_no,
            'status' => 'active',
        ];
    }
}

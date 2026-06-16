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
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\ClassStudentService;

/**
 * @internal
 * @coversNothing
 */
final class ClassStudentServiceTest extends AcademicTestCase
{
    public function testSaveStudentsRequiresActiveAccount(): void
    {
        [$tenantId, $campusId, $class, $student] = $this->fixture(maxStudents: 10, classType: 'group', createAccounts: false);

        try {
            make(ClassStudentService::class)->saveStudents((int) $class->id, [(int) $student->id], $this->context($tenantId), 901);
            self::fail('Expected missing account to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
            self::assertSame((int) $student->id, $exception->getResponse()->data['student_id']);
        }
    }

    public function testSaveStudentsRejectsMaxStudentOverflow(): void
    {
        [$tenantId, , $class, $studentA, $studentB] = $this->fixture(maxStudents: 1, classType: 'group', createAccounts: true);

        try {
            make(ClassStudentService::class)->saveStudents((int) $class->id, [(int) $studentA->id, (int) $studentB->id], $this->context($tenantId), 901);
            self::fail('Expected max student overflow to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testOneToOneClassAllowsExactlyOneActiveStudent(): void
    {
        [$tenantId, , $class, $studentA, $studentB] = $this->fixture(maxStudents: 0, classType: 'one_to_one', createAccounts: true);

        try {
            make(ClassStudentService::class)->saveStudents((int) $class->id, [(int) $studentA->id, (int) $studentB->id], $this->context($tenantId), 901);
            self::fail('Expected one-to-one class with two active students to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(int $maxStudents, string $classType, bool $createAccounts): array
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
            'class_type' => $classType,
            'max_students' => $maxStudents,
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $studentA = $this->student((int) $tenant->id, (int) $campus->id, 'S001');
        $studentB = $this->student((int) $tenant->id, (int) $campus->id, 'S002');
        if ($createAccounts) {
            $this->account((int) $tenant->id, (int) $campus->id, (int) $studentA->id, (int) $course->id);
            $this->account((int) $tenant->id, (int) $campus->id, (int) $studentB->id, (int) $course->id);
        }

        return [(int) $tenant->id, (int) $campus->id, $class, $studentA, $studentB];
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

    private function account(int $tenantId, int $campusId, int $studentId, int $courseId): void
    {
        EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'available_units' => '10.00',
            'status' => 'active',
        ]);
    }
}

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
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\TeacherCourseService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherCourseServiceTest extends AcademicTestCase
{
    public function testSaveTeachersRejectsDisabledTeacher(): void
    {
        [$tenantId, $campusId, $course] = $this->courseFixture();
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'teacher_no' => 'T001',
            'name' => 'Teacher',
            'status' => 'disabled',
        ]);

        try {
            make(TeacherCourseService::class)->saveTeachers((int) $course->id, [(int) $teacher->id], $this->context($tenantId), 901);
            self::fail('Expected disabled teacher to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
            self::assertSame((int) $teacher->id, $exception->getResponse()->data['teacher_id']);
        }
    }

    public function testSaveTeachersRejectsTeacherOutsideCampusScope(): void
    {
        $tenant = $this->tenant('tenant');
        $allowedCampus = $this->campus($tenant, 'allowed');
        $blockedCampus = $this->campus($tenant, 'blocked');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $allowedCampus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $blockedCampus->id,
            'teacher_no' => 'T001',
            'name' => 'Teacher',
            'status' => 'enabled',
        ]);

        try {
            make(TeacherCourseService::class)->saveTeachers(
                (int) $course->id,
                [(int) $teacher->id],
                $this->context((int) $tenant->id, EducationRoleCode::AcademicStaff, [(int) $allowedCampus->id]),
                901
            );
            self::fail('Expected teacher outside campus scope to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame((int) $teacher->id, $exception->getResponse()->data['teacher_id']);
        }
    }

    /**
     * @return array{int, int, EducationCourse}
     */
    private function courseFixture(): array
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

        return [(int) $tenant->id, (int) $campus->id, $course];
    }
}

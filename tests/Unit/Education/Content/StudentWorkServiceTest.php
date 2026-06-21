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

namespace HyperfTests\Unit\Education\Content;

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\StudentWorkService;

/**
 * @internal
 * @coversNothing
 */
final class StudentWorkServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_student_work_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-student-work');
        $service = make(StudentWorkService::class);
        $visible = $service->saveForTeacher([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'lesson_id' => 8801,
            'teacher_id' => 701,
            'title' => 'Visible work',
            'description' => 'current campus work',
        ], [1201]);
        $service->saveForTeacher([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'student_id' => 1202,
            'lesson_id' => 8802,
            'teacher_id' => 702,
            'title' => 'Hidden work',
            'description' => 'other campus work',
        ], [1202]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9907);

        $page = $service->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame($visible['student_work_id'], (int) $page['list'][0]['id']);
    }

    public function testTeacherUploadsWorkForAssignedStudentOnly(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_student_work');
        $service = make(StudentWorkService::class);
        $created = $service->saveForTeacher([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'lesson_id' => 8801,
            'teacher_id' => 701,
            'title' => 'Line work',
            'description' => 'first draft',
            'attachment_ids' => [9001],
        ], [1201]);

        self::assertSame('draft', $created['status']);
        self::assertGreaterThan(0, $created['student_work_id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('student is not assigned to current teacher');

        $service->saveForTeacher([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1202,
            'teacher_id' => 701,
            'title' => 'Hidden work',
        ], [1201]);
    }
}

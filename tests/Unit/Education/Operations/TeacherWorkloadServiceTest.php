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

namespace HyperfTests\Unit\Education\Operations;

use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Service\Education\Operations\TeacherWorkloadService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherWorkloadServiceTest extends OperationsTestCase
{
    public function testSubstituteWorkloadDoesNotOverwriteMainTeacher(): void
    {
        $tenant = $this->tenant('ops_workload');
        $campus = $this->campus($tenant);
        $service = make(TeacherWorkloadService::class);
        $service->recordLessonTeacher([
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'teacher_id' => 2001,
            'lesson_id' => 3001,
            'workload_type' => 'main',
            'credits' => '1.00',
            'student_count' => 3,
        ]);
        $service->recordLessonTeacher([
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'teacher_id' => 2002,
            'lesson_id' => 3001,
            'workload_type' => 'substitute',
            'credits' => '1.00',
            'student_count' => 3,
        ]);

        self::assertSame(1, EducationTeacherWorkloadRecord::query()->where('tenant_id', $tenant->id)->where('workload_type', 'main')->count());
        self::assertSame(1, EducationTeacherWorkloadRecord::query()->where('tenant_id', $tenant->id)->where('workload_type', 'substitute')->count());
    }
}

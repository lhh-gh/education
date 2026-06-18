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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Repository\Education\Academic\LessonPackageRepository;

/**
 * @internal
 * @coversNothing
 */
final class LessonPackageRepositoryTest extends AcademicTestCase
{
    public function testPageFiltersByCourseAndStatus(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $courseA = $this->course((int) $tenant->id, (int) $campus->id, 'ART-001');
        $courseB = $this->course((int) $tenant->id, (int) $campus->id, 'MUS-001');
        $visible = $this->package((int) $tenant->id, (int) $campus->id, (int) $courseA->id, 'ART-24', 'enabled');
        $this->package((int) $tenant->id, (int) $campus->id, (int) $courseA->id, 'ART-12', 'disabled');
        $this->package((int) $tenant->id, (int) $campus->id, (int) $courseB->id, 'MUS-24', 'enabled');

        $result = make(LessonPackageRepository::class)->pageByContext([
            'campus_id' => $campus->id,
            'course_id' => $courseA->id,
            'status' => 'enabled',
        ], 1, 20, $this->context((int) $tenant->id));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    private function course(int $tenantId, int $campusId, string $code): EducationCourse
    {
        return EducationCourse::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'code' => $code,
            'name' => $code,
            'status' => 'enabled',
        ]);
    }

    private function package(int $tenantId, int $campusId, int $courseId, string $code, string $status): EducationLessonPackage
    {
        return EducationLessonPackage::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'code' => $code,
            'name' => $code,
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'status' => $status,
        ]);
    }
}

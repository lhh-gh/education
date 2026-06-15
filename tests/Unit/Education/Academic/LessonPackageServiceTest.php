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
use App\Service\Education\Academic\LessonPackageService;

/**
 * @internal
 * @coversNothing
 */
final class LessonPackageServiceTest extends AcademicTestCase
{
    public function testCreateComputesTotalUnits(): void
    {
        [$tenantId, $campusId, $course] = $this->courseFixture('enabled');

        $package = make(LessonPackageService::class)->create([
            'campus_id' => $campusId,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => '20',
            'bonus_units' => '4',
            'list_price' => '3600',
            'sale_price' => '3000',
        ], $this->context($tenantId), 901);

        self::assertSame('24.00', $package->total_units);
    }

    public function testCreateRejectsZeroTotalUnits(): void
    {
        [$tenantId, $campusId, $course] = $this->courseFixture('enabled');

        try {
            make(LessonPackageService::class)->create([
                'campus_id' => $campusId,
                'course_id' => $course->id,
                'code' => 'ART-00',
                'name' => 'Zero Lessons',
                'lesson_units' => '0',
                'bonus_units' => '0',
                'list_price' => '0',
                'sale_price' => '0',
            ], $this->context($tenantId), 901);
            self::fail('Expected zero total units to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testCreateRejectsDisabledCourse(): void
    {
        [$tenantId, $campusId, $course] = $this->courseFixture('disabled');

        try {
            make(LessonPackageService::class)->create([
                'campus_id' => $campusId,
                'course_id' => $course->id,
                'code' => 'ART-24',
                'name' => '24 Lessons',
                'lesson_units' => '20',
                'bonus_units' => '4',
                'list_price' => '3600',
                'sale_price' => '3000',
            ], $this->context($tenantId), 901);
            self::fail('Expected disabled course to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    /**
     * @return array{int, int, EducationCourse}
     */
    private function courseFixture(string $status): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => $status,
        ]);

        return [(int) $tenant->id, (int) $campus->id, $course];
    }
}

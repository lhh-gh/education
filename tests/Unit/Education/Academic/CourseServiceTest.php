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
use App\Model\Education\Academic\EducationLessonPackage;
use App\Service\Education\Academic\CourseService;

/**
 * @internal
 * @coversNothing
 */
final class CourseServiceTest extends AcademicTestCase
{
    public function testCreateRejectsDuplicateCourseCode(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);

        try {
            make(CourseService::class)->create([
                'campus_id' => $campus->id,
                'code' => 'ART-001',
                'name' => 'Art Duplicate',
            ], $this->context((int) $tenant->id), 901);
            self::fail('Expected duplicate course code to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('ART-001', $exception->getResponse()->data['code']);
        }
    }

    public function testDeleteRejectsCourseWithPackageReference(): void
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
        EducationLessonPackage::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'ART-24',
            'name' => '24 Lessons',
            'lesson_units' => '20.00',
            'bonus_units' => '4.00',
            'total_units' => '24.00',
            'list_price' => '3600.00',
            'sale_price' => '3000.00',
            'status' => 'enabled',
        ]);

        try {
            make(CourseService::class)->delete((int) $course->id, $this->context((int) $tenant->id), 901);
            self::fail('Expected referenced course delete to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }
}

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

use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\EnrollmentRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class EnrollmentRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('enrollment_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = $this->enrollment((int) $tenant->id, (int) $campusA->id, 'ENR-A');
        $this->enrollment((int) $tenant->id, (int) $campusB->id, 'ENR-B');

        $result = make(EnrollmentRepository::class)->pageByContext([], 1, 20, new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testNextEnrollmentNoIsUniquePerCall(): void
    {
        $repository = make(EnrollmentRepository::class);

        $first = $repository->nextEnrollmentNo(1001, 2001);
        $second = $repository->nextEnrollmentNo(1001, 2001);

        self::assertStringStartsWith('ENR', $first);
        self::assertStringStartsWith('ENR', $second);
        self::assertNotSame($first, $second);
    }

    private function enrollment(int $tenantId, int $campusId, string $enrollmentNo): EducationEnrollment
    {
        return EducationEnrollment::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'enrollment_no' => $enrollmentNo,
            'student_id' => 1001,
            'course_id' => 2001,
            'lesson_package_id' => 3001,
            'student_name_snapshot' => 'Student',
            'course_name_snapshot' => 'Course',
            'package_name_snapshot' => 'Package',
            'package_lesson_units' => '20.00',
            'package_bonus_units' => '0.00',
            'total_units' => '20.00',
            'list_price' => '3000.00',
            'deal_amount' => '2800.00',
            'status' => 'pending',
            'enrolled_at' => '2026-06-15 09:00:00',
        ]);
    }
}

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

use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\ConsumptionRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class ConsumptionRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('consumption_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = $this->consumption((int) $tenant->id, (int) $campusA->id, 'CON-101', 101, 'attendance', 'active');
        $this->consumption((int) $tenant->id, (int) $campusB->id, 'CON-102', 102, 'attendance', 'active');

        $result = make(ConsumptionRepository::class)->page([], new EducationUserContext(
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

    public function testPageFiltersByAccountSourceStatusAndKeyword(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $visible = $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-001', 101, 'attendance', 'active', 'lesson charge');
        $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-002', 102, 'attendance', 'active', 'lesson charge');
        $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-003', 101, 'rollback', 'active', 'lesson charge');
        $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-004', 101, 'attendance', 'reversed', 'lesson charge');

        $result = make(ConsumptionRepository::class)->page([
            'account_id' => 101,
            'source_type' => 'attendance',
            'status' => 'active',
            'keyword' => 'CON-001',
        ], $this->context((int) $tenant->id));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testHasRollbackDetectsExistingReversal(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $original = $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-001', 101, 'attendance', 'active');
        $this->consumption((int) $tenant->id, (int) $campus->id, 'CON-002', 101, 'rollback', 'active', originalId: (int) $original->id);

        self::assertTrue(make(ConsumptionRepository::class)->hasRollback((int) $original->id, (int) $tenant->id));
    }

    private function consumption(int $tenantId, int $campusId, string $no, int $accountId, string $sourceType, string $status, string $reason = 'reason', ?int $originalId = null): EducationLessonConsumption
    {
        return EducationLessonConsumption::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'consumption_no' => $no,
            'account_id' => $accountId,
            'student_id' => 201,
            'course_id' => 301,
            'lesson_id' => 401,
            'lesson_student_id' => 501,
            'attendance_id' => $sourceType === 'attendance' ? (int) str_replace('CON-', '', $no) : $accountId + 1000,
            'source_type' => $sourceType,
            'direction' => $sourceType === 'rollback' ? 'increase' : 'decrease',
            'units' => '1.00',
            'before_available_units' => '10.00',
            'after_available_units' => '9.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => '1.00',
            'status' => $status,
            'original_consumption_id' => $originalId,
            'reason' => $reason,
        ]);
    }
}

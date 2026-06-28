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

use App\Model\Education\Academic\EducationAccountAdjustment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\AccountAdjustmentRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class AccountAdjustmentRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('adjustment_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = $this->adjustment((int) $tenant->id, (int) $campusA->id, 'ADJ-A', 101, 'supplement_deduction', 'confirmed');
        $this->adjustment((int) $tenant->id, (int) $campusB->id, 'ADJ-B', 102, 'supplement_deduction', 'confirmed');

        $result = make(AccountAdjustmentRepository::class)->page([], new EducationUserContext(
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

    public function testPageFiltersByAccountTypeStatusAndKeyword(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $visible = $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-001', 101, 'supplement_deduction', 'confirmed', 'material');
        $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-002', 102, 'supplement_deduction', 'confirmed', 'material');
        $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-003', 101, 'rollback', 'confirmed', 'material');
        $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-004', 101, 'supplement_deduction', 'rolled_back', 'material');

        $result = make(AccountAdjustmentRepository::class)->page([
            'account_id' => 101,
            'adjustment_type' => 'supplement_deduction',
            'status' => 'confirmed',
            'keyword' => 'ADJ-001',
        ], $this->context((int) $tenant->id));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testHasRollbackDetectsExistingAdjustmentReversal(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $original = $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-001', 101, 'supplement_deduction', 'confirmed');
        $this->adjustment((int) $tenant->id, (int) $campus->id, 'ADJ-002', 101, 'rollback', 'confirmed', originalId: (int) $original->id);

        self::assertTrue(make(AccountAdjustmentRepository::class)->hasRollback((int) $original->id, (int) $tenant->id));
    }

    private function adjustment(int $tenantId, int $campusId, string $no, int $accountId, string $type, string $status, string $reason = 'reason', ?int $originalId = null): EducationAccountAdjustment
    {
        return EducationAccountAdjustment::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'adjustment_no' => $no,
            'account_id' => $accountId,
            'student_id' => 201,
            'course_id' => 301,
            'adjustment_type' => $type,
            'direction' => $type === 'rollback' ? 'increase' : 'decrease',
            'units' => '1.00',
            'before_available_units' => '10.00',
            'after_available_units' => '9.00',
            'before_adjusted_units' => '0.00',
            'after_adjusted_units' => '-1.00',
            'status' => $status,
            'original_adjustment_id' => $originalId,
            'reason' => $reason,
        ]);
    }
}

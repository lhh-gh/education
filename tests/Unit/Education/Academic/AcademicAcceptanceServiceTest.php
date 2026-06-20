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
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\AcademicAcceptanceService;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class AcademicAcceptanceServiceTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersLedgerWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('acceptance_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $courseA = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campusA->id, 'code' => 'ART-A', 'name' => 'Art A', 'status' => 'enabled']);
        $courseB = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campusB->id, 'code' => 'ART-B', 'name' => 'Art B', 'status' => 'enabled']);
        $studentA = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campusA->id, 'student_no' => 'S001', 'name' => 'Student A', 'status' => 'enabled']);
        $studentB = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campusB->id, 'student_no' => 'S002', 'name' => 'Student B', 'status' => 'enabled']);
        $this->account((int) $tenant->id, (int) $campusA->id, (int) $studentA->id, (int) $courseA->id);
        $this->account((int) $tenant->id, (int) $campusB->id, (int) $studentB->id, (int) $courseB->id);

        $summary = make(AcademicAcceptanceService::class)->summary([], new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $summary['ledger']['account_count']);
    }

    public function testGateCatalogContainsRequiredV1Gates(): void
    {
        $catalog = make(AcademicAcceptanceService::class)->requiredGateCatalog();

        self::assertArrayHasKey('foundation_context_ready', $catalog);
        self::assertArrayHasKey('guardian_mobile_ready', $catalog);
        self::assertArrayHasKey('reports_ready', $catalog);
        self::assertArrayHasKey('ledger_consistent', $catalog);
        self::assertCount(14, $catalog);
    }

    public function testLedgerMismatchReturnsFailStatus(): void
    {
        $tenant = $this->tenant('acceptance_mismatch');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART', 'name' => 'Art', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S001', 'name' => 'Student', 'status' => 'enabled']);
        EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '5.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '10.00',
            'status' => 'active',
        ]);

        $summary = make(AcademicAcceptanceService::class)->summary([], $this->context((int) $tenant->id, campusIds: [(int) $campus->id]));

        self::assertSame('fail', $summary['overall_status']);
        self::assertSame(1, $summary['ledger']['mismatch_count']);
        self::assertSame('ledger_consistent', array_values(array_filter($summary['gates'], static fn (array $gate): bool => $gate['key'] === 'ledger_consistent'))[0]['key']);
    }

    private function account(int $tenantId, int $campusId, int $studentId, int $courseId): EducationStudentCourseAccount
    {
        return EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '0.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '10.00',
            'status' => 'active',
        ]);
    }
}

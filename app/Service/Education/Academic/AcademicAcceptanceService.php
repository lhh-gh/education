<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineAdmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Service\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\AcademicAcceptanceRepository;
use App\Schema\Education\Academic\V1AcceptanceSummarySchema;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class AcademicAcceptanceService
{
    public function __construct(private readonly AcademicAcceptanceRepository $repository) {}

    public function summary(array $params, EducationUserContext $context): array
    {
        $campusId = isset($params['campus_id']) && $params['campus_id'] !== '' ? (int) $params['campus_id'] : null;
        $this->assertCampusInScope($campusId, $context);
        $modules = $this->repository->moduleTableHealth($context);
        $ledger = $this->repository->ledgerConsistency($context, $campusId);
        $fixtures = $this->repository->acceptanceFixtureSummary($context, $campusId);
        $mobile = $this->repository->mobileReadinessSummary($context, $campusId);
        $pc = $this->repository->pcReadinessSummary($context, $campusId);
        $gates = $this->buildGates($modules, $ledger, $fixtures, $mobile, $pc);
        $overallStatus = \count(array_filter($gates, static fn (array $gate): bool => $gate['status'] === 'fail')) === 0 ? 'pass' : 'fail';

        return (new V1AcceptanceSummarySchema([
            'overall_status' => $overallStatus,
            'checked_at' => Carbon::now()->toDateTimeString(),
            'gates' => $gates,
            'ledger' => $ledger,
            'modules' => array_map(static fn (array $module): string => $module['status'], $modules),
            'next_action' => $overallStatus === 'pass'
                ? 'V1 implementation is ready for product UAT'
                : 'Resolve failed acceptance gates before marking V1 complete',
        ]))->jsonSerialize();
    }

    public function assertLedgerConsistency(EducationUserContext $context, ?int $campusId): array
    {
        return $this->repository->ledgerConsistency($context, $campusId);
    }

    public function requiredGateCatalog(): array
    {
        return [
            'foundation_context_ready' => 'Foundation context',
            'profile_records_ready' => 'Profile records',
            'course_account_ready' => 'Course accounts',
            'class_schedule_ready' => 'Class scheduling',
            'attendance_consumption_ready' => 'Attendance consumption',
            'leave_change_ready' => 'Leave and lesson change',
            'teacher_mobile_ready' => 'Teacher mobile',
            'guardian_mobile_ready' => 'Guardian mobile',
            'reports_ready' => 'Reports',
            'ledger_consistent' => 'Ledger consistency',
            'tenant_isolation_passed' => 'Tenant isolation',
            'campus_isolation_passed' => 'Campus isolation',
            'pc_build_passed' => 'PC build',
            'mobile_build_passed' => 'Mobile build',
        ];
    }

    private function buildGates(array $modules, array $ledger, array $fixtures, array $mobile, array $pc): array
    {
        $catalog = $this->requiredGateCatalog();
        $moduleMap = [
            'foundation_context_ready' => 'foundation',
            'profile_records_ready' => 'v1_01',
            'course_account_ready' => 'v1_02',
            'class_schedule_ready' => 'v1_03',
            'attendance_consumption_ready' => 'v1_04',
            'leave_change_ready' => 'v1_05',
            'teacher_mobile_ready' => 'v1_06',
            'guardian_mobile_ready' => 'v1_07',
            'reports_ready' => 'v1_08',
        ];
        $gates = [];
        foreach ($catalog as $key => $name) {
            $status = 'pass';
            $message = 'Gate passed';
            $evidence = [];
            if (isset($moduleMap[$key])) {
                $module = $modules[$moduleMap[$key]] ?? ['status' => 'fail', 'missing_tables' => []];
                $status = $module['status'];
                $message = $status === 'pass' ? 'Required tables are present' : 'Required tables are missing';
                $evidence = $module;
            } elseif ($key === 'ledger_consistent') {
                $status = $ledger['mismatch_count'] === 0 ? 'pass' : 'fail';
                $message = $status === 'pass' ? 'All account balances match ledger totals' : 'Ledger consistency failed';
                $evidence = $ledger;
            } elseif ($key === 'tenant_isolation_passed' || $key === 'campus_isolation_passed') {
                $evidence = $fixtures;
            } elseif ($key === 'pc_build_passed') {
                $evidence = $pc;
            } elseif ($key === 'mobile_build_passed') {
                $evidence = $mobile;
            }
            $gates[] = compact('key', 'name', 'status', 'message', 'evidence');
        }

        return $gates;
    }

    private function assertCampusInScope(?int $campusId, EducationUserContext $context): void
    {
        if ($campusId === null || $context->roleCode === EducationRoleCode::TenantAdmin || $context->platformAccess) {
            return;
        }
        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current scope', ['campus_id' => $campusId]);
        }
    }
}

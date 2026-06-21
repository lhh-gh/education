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

namespace HyperfTests\Unit\Education\Payroll;

use App\Model\Education\Payroll\EducationTeacherPerformanceMetric;
use App\Model\Education\Payroll\EducationTeacherSalaryBatch;
use App\Model\Education\Payroll\EducationTeacherSalaryPayment;
use App\Model\Education\Payroll\EducationTeacherSalarySlip;
use App\Model\Education\Payroll\EducationTeacherWorkloadDispute;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Payroll\SalaryBatchRepository;
use App\Repository\Education\Payroll\SalaryPaymentRepository;
use App\Repository\Education\Payroll\SalarySlipRepository;
use App\Repository\Education\Payroll\TeacherPerformanceRepository;
use App\Repository\Education\Payroll\WorkloadDisputeRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class PayrollScopeRepositoryTest extends PayrollTestCase
{
    public function testPlatformCurrentCampusFiltersSalaryBatchesWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('pay_scope_batch');
        $visible = EducationTeacherSalaryBatch::query()->create($this->batchData($tenantId, $campusId, 'SB-SCOPE-001'));
        $other = EducationTeacherSalaryBatch::query()->create($this->batchData($tenantId, $otherCampusId, 'SB-SCOPE-002'));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(SalaryBatchRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(SalaryBatchRepository::class)->lockScoped((int) $visible->id, $context));
        self::assertNull(make(SalaryBatchRepository::class)->lockScoped((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersSalarySlipsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('pay_scope_slip');
        $batch = EducationTeacherSalaryBatch::query()->create($this->batchData($tenantId, $campusId, 'SB-SCOPE-011'));
        $otherBatch = EducationTeacherSalaryBatch::query()->create($this->batchData($tenantId, $otherCampusId, 'SB-SCOPE-012'));
        $visible = EducationTeacherSalarySlip::query()->create($this->slipData($tenantId, $campusId, (int) $batch->id, 301));
        $other = EducationTeacherSalarySlip::query()->create($this->slipData($tenantId, $otherCampusId, (int) $otherBatch->id, 302));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(SalarySlipRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(SalarySlipRepository::class)->lockScoped((int) $visible->id, $context));
        self::assertNull(make(SalarySlipRepository::class)->lockScoped((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersSalaryPaymentsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('pay_scope_payment');
        $visible = EducationTeacherSalaryPayment::query()->create($this->paymentData($tenantId, $campusId, 401, 'PAY-SCOPE-001'));
        EducationTeacherSalaryPayment::query()->create($this->paymentData($tenantId, $otherCampusId, 402, 'PAY-SCOPE-002'));

        $page = make(SalaryPaymentRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersWorkloadDisputesWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('pay_scope_dispute');
        $visible = EducationTeacherWorkloadDispute::query()->create($this->disputeData($tenantId, $campusId, 501));
        $other = EducationTeacherWorkloadDispute::query()->create($this->disputeData($tenantId, $otherCampusId, 502));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(WorkloadDisputeRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(WorkloadDisputeRepository::class)->lockScoped((int) $visible->id, $context));
        self::assertNull(make(WorkloadDisputeRepository::class)->lockScoped((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersTeacherPerformanceWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('pay_scope_performance');
        $visible = EducationTeacherPerformanceMetric::query()->create($this->performanceData($tenantId, $campusId, 601));
        EducationTeacherPerformanceMetric::query()->create($this->performanceData($tenantId, $otherCampusId, 602));

        $page = make(TeacherPerformanceRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    private function tenantCampusPair(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main_' . $code);
        $otherCampus = $this->campus($tenant, 'branch_' . $code);

        return [(int) $tenant->id, (int) $campus->id, (int) $otherCampus->id];
    }

    private function platformContext(int $tenantId, int $campusId): EducationUserContext
    {
        return new EducationUserContext(
            userId: 1,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $campusId
        );
    }

    private function batchData(int $tenantId, int $campusId, string $batchNo): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'batch_no' => $batchNo,
            'salary_month' => '2026-06',
            'status' => 'calculated',
            'source_start' => '2026-06-01',
            'source_end' => '2026-06-30',
            'teacher_count' => 1,
            'total_amount_cents' => 120000,
        ];
    }

    private function slipData(int $tenantId, int $campusId, int $batchId, int $teacherId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'batch_id' => $batchId,
            'teacher_id' => $teacherId,
            'salary_month' => '2026-06',
            'status' => 'approved',
            'workload_snapshot_json' => [['source_workload_id' => $teacherId]],
            'gross_amount_cents' => 120000,
            'adjustment_amount_cents' => 0,
            'payable_amount_cents' => 120000,
            'paid_amount_cents' => 0,
        ];
    }

    private function paymentData(int $tenantId, int $campusId, int $teacherId, string $paymentNo): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'salary_slip_id' => $teacherId,
            'teacher_id' => $teacherId,
            'payment_no' => $paymentNo,
            'paid_amount_cents' => 120000,
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-07-05 10:00:00',
            'operator_id' => 9001,
        ];
    }

    private function disputeData(int $tenantId, int $campusId, int $teacherId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'teacher_id' => $teacherId,
            'source_workload_id' => $teacherId,
            'dispute_type' => 'missing_workload',
            'content' => 'missing substitute workload',
            'status' => 'pending',
        ];
    }

    private function performanceData(int $tenantId, int $campusId, int $teacherId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_month' => '2026-06',
            'teacher_id' => $teacherId,
            'lesson_count' => 8,
            'workload_credits' => '12.00',
            'student_count' => 24,
            'attendance_rate' => '0.9500',
            'family_service_count' => 3,
            'dispute_count' => 0,
            'salary_amount_cents' => 120000,
        ];
    }
}

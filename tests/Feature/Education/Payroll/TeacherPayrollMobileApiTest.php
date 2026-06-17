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

namespace HyperfTests\Feature\Education\Payroll;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Payroll\SalaryBatchService;
use App\Service\Education\Payroll\SalaryCalculationService;
use App\Service\Education\Payroll\SalaryReviewService;
use App\Service\Education\Payroll\SalaryRuleService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherPayrollMobileApiTest extends PayrollApiCase
{
    public function testTeacherReadsOnlyOwnSlips(): void
    {
        $tenant = $this->tenant('pay_mobile_api');
        $campus = $this->campus($tenant);
        $profile = $this->createTenantProfile($tenant, EducationRoleCode::Teacher->value, $campus);
        $teacher = $this->teacherFixture($tenant, $campus, userProfileId: (int) $profile->id);
        $otherTeacher = $this->teacherFixture($tenant, $campus, 'Other Teacher');
        $this->workloadFixture($tenant, $campus, $teacher);
        $this->workloadFixture($tenant, $campus, $otherTeacher);
        $context = $this->context((int) $tenant->id, EducationRoleCode::TenantAdmin, [(int) $campus->id], $this->user->id);
        make(SalaryRuleService::class)->save([
            'rule_code' => 'MAIN-001',
            'rule_name' => 'Main workload',
            'campus_id' => $campus->id,
            'effective_start' => '2026-01-01',
            'items' => [['item_type' => 'workload', 'workload_type' => 'main', 'calculation_method' => 'per_credit', 'unit_amount_cents' => 12000]],
        ], $context);
        $batch = make(SalaryCalculationService::class)->calculate(['salary_month' => '2026-06', 'campus_id' => $campus->id], $context);
        make(SalaryBatchService::class)->submitReview((int) $batch['batch_id'], $context);
        make(SalaryReviewService::class)->approveBatch((int) $batch['batch_id'], ['review_note' => 'approved'], $context);
        EducationUserProfile::query()->whereKey($profile->id)->update(['role_code' => EducationRoleCode::Teacher->value]);

        $response = $this->get('/mobile/education/payroll/teacher/slips', [
            'salary_month' => '2026-06',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame((int) $teacher->id, $response['data']['list'][0]['teacher_id']);
    }
}

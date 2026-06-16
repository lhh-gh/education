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
use App\Model\Education\Academic\EducationAccountAdjustment;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\AccountAdjustmentService;

/**
 * @internal
 * @coversNothing
 */
final class AccountAdjustmentServiceTest extends AcademicTestCase
{
    public function testCreateSupplementDeductionDecreasesAdjustedAndAvailableUnits(): void
    {
        [$tenantId, $campusId, $account] = $this->account();

        $result = make(AccountAdjustmentService::class)->createSupplementDeduction([
            'account_id' => $account->id,
            'units' => '2.00',
            'reason' => 'material fee',
        ], $this->context($tenantId, campusIds: [$campusId]), 901);

        self::assertSame('supplement_deduction', $result['adjustment']['adjustment_type']);
        self::assertSame('8.00', $account->refresh()->available_units);
        self::assertSame('-2.00', $account->refresh()->adjusted_units);
    }

    public function testCreateSupplementDeductionRequiresReason(): void
    {
        [$tenantId, $campusId, $account] = $this->account();

        try {
            make(AccountAdjustmentService::class)->createSupplementDeduction([
                'account_id' => $account->id,
                'units' => '1.00',
                'reason' => '',
            ], $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected missing reason to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::UNPROCESSABLE_ENTITY, $exception->getResponse()->code);
        }
    }

    public function testCreateSupplementDeductionRejectsInsufficientBalance(): void
    {
        [$tenantId, $campusId, $account] = $this->account(availableUnits: '0.50');

        try {
            make(AccountAdjustmentService::class)->createSupplementDeduction([
                'account_id' => $account->id,
                'units' => '1.00',
                'reason' => 'material fee',
            ], $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected insufficient balance to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testRollbackAdjustmentCreatesReversalAndRestoresAccount(): void
    {
        [$tenantId, $campusId, $account] = $this->account(availableUnits: '8.00', adjustedUnits: '-2.00');
        $adjustment = $this->adjustment($tenantId, $campusId, (int) $account->id);

        $result = make(AccountAdjustmentService::class)->rollback((int) $adjustment->id, 'wrong account', $this->context($tenantId, campusIds: [$campusId]), 901);

        self::assertSame('rolled_back', $result['original']['status']);
        self::assertSame('rollback', $result['rollback']['adjustment_type']);
        self::assertSame('10.00', $account->refresh()->available_units);
        self::assertSame('0.00', $account->refresh()->adjusted_units);
    }

    public function testAccountBalanceInvariantIsPreservedAfterConsumptionAndAdjustmentRollback(): void
    {
        [$tenantId, $campusId, $account] = $this->account(availableUnits: '8.00', adjustedUnits: '-2.00');
        $adjustment = $this->adjustment($tenantId, $campusId, (int) $account->id);

        make(AccountAdjustmentService::class)->rollback((int) $adjustment->id, 'rollback', $this->context($tenantId, campusIds: [$campusId]), 901);
        $account = $account->refresh();

        $expected = (float) $account->purchased_units + (float) $account->bonus_units + (float) $account->adjusted_units - (float) $account->consumed_units - (float) $account->refunded_units - (float) $account->frozen_units;
        self::assertSame(number_format($expected, 2, '.', ''), $account->available_units);
    }

    private function account(string $availableUnits = '10.00', string $adjustedUnits = '0.00'): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 201,
            'course_id' => 301,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '0.00',
            'adjusted_units' => $adjustedUnits,
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => $availableUnits,
            'status' => 'active',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $account];
    }

    private function adjustment(int $tenantId, int $campusId, int $accountId): EducationAccountAdjustment
    {
        return EducationAccountAdjustment::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'adjustment_no' => 'ADJ-001',
            'account_id' => $accountId,
            'student_id' => 201,
            'course_id' => 301,
            'adjustment_type' => 'supplement_deduction',
            'direction' => 'decrease',
            'units' => '2.00',
            'before_available_units' => '10.00',
            'after_available_units' => '8.00',
            'before_adjusted_units' => '0.00',
            'after_adjusted_units' => '-2.00',
            'status' => 'confirmed',
            'reason' => 'material fee',
        ]);
    }
}

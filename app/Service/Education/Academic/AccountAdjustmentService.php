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

namespace App\Service\Education\Academic;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationAccountAdjustment;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Enums\Education\Academic\StudentCourseAccountStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\AccountAdjustmentRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class AccountAdjustmentService
{
    public function __construct(
        private readonly AccountAdjustmentRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function detail(int $id, EducationUserContext $context): EducationAccountAdjustment
    {
        $row = $this->repository->findScoped($id, $context);
        if (! $row instanceof EducationAccountAdjustment) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'account adjustment not found', ['id' => $id]);
        }

        return $row;
    }

    public function createSupplementDeduction(array $data, EducationUserContext $context, ?int $operatorId): array
    {
        $accountId = (int) ($data['account_id'] ?? 0);
        $units = $this->decimal($data['units'] ?? '0');
        $reason = trim((string) ($data['reason'] ?? ''));
        if ($accountId <= 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'account_id is required');
        }
        if ((float) $units <= 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'adjustment units must be positive');
        }
        if ($reason === '') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'adjustment reason is required');
        }

        return Db::transaction(function () use ($accountId, $units, $reason, $context, $operatorId): array {
            $account = $this->findAccountForWrite($accountId, $context);
            $this->assertActiveAccount($account);
            if ((float) $account->available_units < (float) $units) {
                throw new BusinessException(ResultCode::CONFLICT, 'student course account has insufficient available_units', [
                    'account_id' => (int) $account->id,
                    'available_units' => $account->available_units,
                    'required_units' => $units,
                ]);
            }

            $beforeAvailable = $this->decimal($account->available_units);
            $beforeAdjusted = $this->decimal($account->adjusted_units);
            $afterAvailable = $this->subtract($beforeAvailable, $units);
            $afterAdjusted = $this->subtract($beforeAdjusted, $units);
            $adjustment = $this->repository->createAdjustment([
                'tenant_id' => (int) $account->tenant_id,
                'campus_id' => (int) $account->campus_id,
                'adjustment_no' => $this->repository->nextAdjustmentNo((int) $account->tenant_id, (int) $account->campus_id),
                'account_id' => (int) $account->id,
                'student_id' => (int) $account->student_id,
                'course_id' => (int) $account->course_id,
                'adjustment_type' => 'supplement_deduction',
                'direction' => 'decrease',
                'units' => $units,
                'before_available_units' => $beforeAvailable,
                'after_available_units' => $afterAvailable,
                'before_adjusted_units' => $beforeAdjusted,
                'after_adjusted_units' => $afterAdjusted,
                'status' => 'confirmed',
                'reason' => $reason,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);

            $before = $account->toArray();
            $account->available_units = $afterAvailable;
            $account->adjusted_units = $afterAdjusted;
            $account->updated_by = $operatorId;
            $account->save();
            $this->dispatchAudit(
                action: 'education.academic.account_adjustment.created',
                adjustment: $adjustment->refresh(),
                context: $context,
                before: $before,
                after: ['adjustment' => $adjustment->toArray(), 'account' => $account->refresh()->toArray()],
                summary: 'Account adjustment created'
            );

            return [
                'adjustment' => $adjustment->refresh()->toArray(),
                'account' => $account->refresh()->toArray(),
            ];
        });
    }

    public function rollback(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'rollback reason is required');
        }
        $original = $this->repository->findConfirmedForRollback($id, $context);
        if (! $original instanceof EducationAccountAdjustment) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'account adjustment not found', ['id' => $id]);
        }
        if ($original->status === 'rolled_back') {
            throw new BusinessException(ResultCode::CONFLICT, 'account adjustment is already rolled back', ['id' => $id]);
        }
        if ($this->repository->hasRollback((int) $original->id, (int) $original->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'account adjustment rollback already exists', ['id' => $id]);
        }

        return Db::transaction(function () use ($original, $reason, $context, $operatorId): array {
            $account = $this->findAccountForWrite((int) $original->account_id, $context);
            $this->assertActiveAccount($account);

            $beforeAvailable = $this->decimal($account->available_units);
            $beforeAdjusted = $this->decimal($account->adjusted_units);
            $afterAvailable = $this->add($beforeAvailable, $original->units);
            $afterAdjusted = $this->add($beforeAdjusted, $original->units);
            $rollback = $this->repository->createRollback([
                'tenant_id' => (int) $original->tenant_id,
                'campus_id' => (int) $original->campus_id,
                'adjustment_no' => $this->repository->nextAdjustmentNo((int) $original->tenant_id, (int) $original->campus_id),
                'account_id' => (int) $original->account_id,
                'student_id' => (int) $original->student_id,
                'course_id' => (int) $original->course_id,
                'adjustment_type' => 'rollback',
                'direction' => 'increase',
                'units' => $this->decimal($original->units),
                'before_available_units' => $beforeAvailable,
                'after_available_units' => $afterAvailable,
                'before_adjusted_units' => $beforeAdjusted,
                'after_adjusted_units' => $afterAdjusted,
                'status' => 'confirmed',
                'original_adjustment_id' => (int) $original->id,
                'reason' => $reason,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);

            $before = [
                'original' => $original->toArray(),
                'account' => $account->toArray(),
            ];
            $account->available_units = $afterAvailable;
            $account->adjusted_units = $afterAdjusted;
            $account->updated_by = $operatorId;
            $account->save();
            $original->status = 'rolled_back';
            $original->rolled_back_at = Carbon::now()->toDateTimeString();
            $original->rolled_back_by = $operatorId;
            $original->updated_by = $operatorId;
            $original->save();

            $this->dispatchAudit(
                action: 'education.academic.account_adjustment.rollback',
                adjustment: $original->refresh(),
                context: $context,
                before: $before,
                after: [
                    'original' => $original->toArray(),
                    'rollback' => $rollback->refresh()->toArray(),
                    'account' => $account->refresh()->toArray(),
                ],
                summary: 'Account adjustment rollback'
            );

            return [
                'original' => $original->refresh()->toArray(),
                'rollback' => $rollback->refresh()->toArray(),
                'account' => $account->refresh()->toArray(),
            ];
        });
    }

    private function findAccountForWrite(int $id, EducationUserContext $context): EducationStudentCourseAccount
    {
        $account = EducationStudentCourseAccount::query()->whereKey($id)->lockForUpdate()->first();
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'student course account not found', ['id' => $id]);
        }
        $this->assertAccountVisible($account, $context);

        return $account;
    }

    private function assertAccountVisible(EducationStudentCourseAccount $account, EducationUserContext $context): void
    {
        if ($context->platformAccess) {
            return;
        }
        if ($context->tenantId !== (int) $account->tenant_id) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $account->tenant_id]);
        }
        if ($context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus((int) $account->campus_id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => (int) $account->campus_id]);
        }
    }

    private function assertActiveAccount(EducationStudentCourseAccount $account): void
    {
        if ($account->status !== StudentCourseAccountStatus::Active->value) {
            throw new BusinessException(ResultCode::CONFLICT, 'student course account is not active', [
                'account_id' => (int) $account->id,
                'status' => $account->status,
            ]);
        }
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function add(string $left, string $right): string
    {
        return number_format((float) $left + (float) $right, 2, '.', '');
    }

    private function subtract(string $left, string $right): string
    {
        return number_format((float) $left - (float) $right, 2, '.', '');
    }

    private function dispatchAudit(string $action, EducationAccountAdjustment $adjustment, EducationUserContext $context, array $before, array $after, string $summary): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'account_adjustment',
            action: $action,
            businessType: 'account_adjustment',
            businessId: (int) $adjustment->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $adjustment->tenant_id, 'campus_id' => (int) $adjustment->campus_id],
            summary: $summary
        ));
    }
}

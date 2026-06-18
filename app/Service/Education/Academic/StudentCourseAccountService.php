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
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationLessonPackage;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Enums\Education\Academic\StudentCourseAccountStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\StudentCourseAccountRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class StudentCourseAccountService
{
    public function __construct(
        private readonly StudentCourseAccountRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        [$page, $pageSize, $filters] = $this->extractPage($filters);

        return $this->repository->pageByContext($filters, $page, $pageSize, $context);
    }

    public function ledger(int $id, array $filters, EducationUserContext $context): array
    {
        return $this->repository->ledger($id, $filters, $context);
    }

    public function changeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
    {
        $account = $this->findForWrite($id, $context);
        $status = $this->normalizeStatus($status);
        if ($account->status === StudentCourseAccountStatus::Closed->value && $status !== StudentCourseAccountStatus::Closed->value) {
            throw new BusinessException(ResultCode::CONFLICT, 'closed account cannot be reopened', ['id' => (int) $account->id]);
        }
        if ($status === StudentCourseAccountStatus::Closed->value && (float) $account->available_units !== 0.0) {
            throw new BusinessException(ResultCode::CONFLICT, 'account can be closed only when available units are zero', ['id' => (int) $account->id, 'available_units' => $account->available_units]);
        }

        return Db::transaction(function () use ($account, $status, $context, $operatorId): EducationStudentCourseAccount {
            $before = $account->toArray();
            $account->fill([
                'status' => $status,
                'updated_by' => $operatorId,
            ]);
            $account->save();
            $account = $account->refresh();
            $this->dispatchAudit('status_changed', $account, $context, $before, $account->toArray());

            return $account;
        });
    }

    public function materializeEnrollment(int $enrollmentId, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
    {
        $enrollment = EducationEnrollment::query()->whereKey($enrollmentId)->lockForUpdate()->first();
        if (! $enrollment instanceof EducationEnrollment) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'enrollment not found in current context', ['id' => $enrollmentId]);
        }
        $this->assertEnrollmentVisible($enrollment, $context);

        if ($enrollment->account_id !== null) {
            $existing = $this->repository->findScoped((int) $enrollment->account_id, $context);
            if ($existing instanceof EducationStudentCourseAccount) {
                return $existing;
            }
        }

        $account = $this->repository->findByStudentCourseForUpdate(
            (int) $enrollment->tenant_id,
            (int) $enrollment->campus_id,
            (int) $enrollment->student_id,
            (int) $enrollment->course_id
        );
        $now = Carbon::now();
        $package = EducationLessonPackage::query()->whereKey($enrollment->lesson_package_id)->first();
        $expiresAt = $package instanceof EducationLessonPackage && $package->validity_days !== null
            ? $now->copy()->addDays((int) $package->validity_days)->toDateTimeString()
            : null;

        if (! $account instanceof EducationStudentCourseAccount) {
            return $this->repository->createForEnrollment([
                'tenant_id' => (int) $enrollment->tenant_id,
                'campus_id' => (int) $enrollment->campus_id,
                'student_id' => (int) $enrollment->student_id,
                'course_id' => (int) $enrollment->course_id,
                'purchased_units' => $this->decimal($enrollment->package_lesson_units),
                'bonus_units' => $this->decimal($enrollment->package_bonus_units),
                'consumed_units' => '0.00',
                'adjusted_units' => '0.00',
                'refunded_units' => '0.00',
                'frozen_units' => '0.00',
                'available_units' => $this->decimal($enrollment->total_units),
                'status' => StudentCourseAccountStatus::Active->value,
                'first_enrollment_id' => (int) $enrollment->id,
                'last_enrollment_id' => (int) $enrollment->id,
                'opened_at' => $now->toDateTimeString(),
                'expires_at' => $expiresAt,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ])->refresh();
        }

        $account->fill([
            'purchased_units' => $this->add($account->purchased_units, $enrollment->package_lesson_units),
            'bonus_units' => $this->add($account->bonus_units, $enrollment->package_bonus_units),
            'available_units' => $this->add($account->available_units, $enrollment->total_units),
            'last_enrollment_id' => (int) $enrollment->id,
            'expires_at' => $this->laterExpiry($account->expires_at?->toDateTimeString(), $expiresAt),
            'updated_by' => $operatorId,
        ]);
        if ($account->first_enrollment_id === null) {
            $account->first_enrollment_id = (int) $enrollment->id;
        }
        if ($account->opened_at === null) {
            $account->opened_at = $now->toDateTimeString();
        }
        $account->save();

        return $account->refresh();
    }

    public function reverseEnrollment(EducationEnrollment $enrollment, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
    {
        if ($enrollment->account_id === null) {
            throw new BusinessException(ResultCode::CONFLICT, 'enrollment has no materialized account', ['id' => (int) $enrollment->id]);
        }

        $account = $this->repository->findScoped((int) $enrollment->account_id, $context);
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'student course account not found in current context', ['id' => (int) $enrollment->account_id]);
        }
        $account = $this->repository->findByStudentCourseForUpdate((int) $account->tenant_id, (int) $account->campus_id, (int) $account->student_id, (int) $account->course_id);
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'student course account not found in current context', ['id' => (int) $enrollment->account_id]);
        }
        if ($account->status === StudentCourseAccountStatus::Closed->value) {
            throw new BusinessException(ResultCode::CONFLICT, 'account is closed', ['id' => (int) $account->id]);
        }
        if ((float) $account->available_units < (float) $enrollment->total_units) {
            throw new BusinessException(ResultCode::CONFLICT, 'enrollment units are already consumed or frozen', [
                'id' => (int) $enrollment->id,
                'available_units' => $account->available_units,
                'required_units' => $enrollment->total_units,
            ]);
        }

        $account->fill([
            'refunded_units' => $this->add($account->refunded_units, $enrollment->total_units),
            'available_units' => $this->subtract($account->available_units, $enrollment->total_units),
            'updated_by' => $operatorId,
        ]);
        $account->save();

        return $account->refresh();
    }

    public function assertAccountCanConsume(int $accountId, string $units, EducationUserContext $context): void
    {
        $account = $this->repository->findScoped($accountId, $context);
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'student course account not found in current context', ['id' => $accountId]);
        }
        if ($account->status !== StudentCourseAccountStatus::Active->value) {
            throw new BusinessException(ResultCode::CONFLICT, 'account is not active', ['id' => $accountId]);
        }
        if ((float) $account->available_units < (float) $units) {
            throw new BusinessException(ResultCode::CONFLICT, 'account available units are insufficient', ['id' => $accountId, 'available_units' => $account->available_units, 'required_units' => $this->decimal($units)]);
        }
    }

    public function consumeUnits(int $accountId, string $units, string $sourceNo, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
    {
        $this->assertAccountCanConsume($accountId, $units, $context);
        $account = $this->findForWrite($accountId, $context);
        $account->fill([
            'consumed_units' => $this->add($account->consumed_units, $units),
            'available_units' => $this->subtract($account->available_units, $units),
            'updated_by' => $operatorId,
            'remark' => $sourceNo,
        ]);
        $account->save();

        return $account->refresh();
    }

    public function adjustUnits(int $accountId, string $units, string $reason, EducationUserContext $context, ?int $operatorId): EducationStudentCourseAccount
    {
        $account = $this->findForWrite($accountId, $context);
        $account->fill([
            'adjusted_units' => $this->add($account->adjusted_units, $units),
            'available_units' => $this->add($account->available_units, $units),
            'updated_by' => $operatorId,
            'remark' => $reason,
        ]);
        $account->save();

        return $account->refresh();
    }

    private function findForWrite(int $id, EducationUserContext $context): EducationStudentCourseAccount
    {
        $account = $this->repository->findScoped($id, $context);
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'student course account not found in current context', ['id' => $id]);
        }

        return $account;
    }

    private function assertEnrollmentVisible(EducationEnrollment $enrollment, EducationUserContext $context): void
    {
        if ($context->platformAccess) {
            return;
        }
        if ($context->tenantId !== (int) $enrollment->tenant_id) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $enrollment->tenant_id]);
        }
        if ($context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus((int) $enrollment->campus_id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => (int) $enrollment->campus_id]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        if (StudentCourseAccountStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid account status');
        }

        return $status;
    }

    private function add(mixed $left, mixed $right): string
    {
        return $this->decimal((float) $left + (float) $right);
    }

    private function subtract(mixed $left, mixed $right): string
    {
        return $this->decimal((float) $left - (float) $right);
    }

    private function decimal(mixed $value): string
    {
        return number_format(round((float) $value, 2), 2, '.', '');
    }

    private function laterExpiry(?string $current, ?string $candidate): ?string
    {
        if ($current === null || $current === '') {
            return $candidate;
        }
        if ($candidate === null || $candidate === '') {
            return $current;
        }

        return Carbon::parse($candidate)->greaterThan(Carbon::parse($current)) ? $candidate : $current;
    }

    private function extractPage(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? $filters['per_page'] ?? 15)));
        unset($filters['page'], $filters['pageSize'], $filters['page_size'], $filters['per_page']);

        return [$page, $pageSize, $filters];
    }

    private function dispatchAudit(string $action, EducationStudentCourseAccount $account, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'student_course_account',
            action: 'education.academic.student_course_account.' . $action,
            businessType: 'student_course_account',
            businessId: (int) $account->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $account->tenant_id, 'campus_id' => (int) $account->campus_id],
            summary: \sprintf('Student course account %d %s', (int) $account->id, $action)
        ));
    }
}

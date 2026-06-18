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

namespace App\Repository\Education\Academic;

use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationEnrollment>
 */
final class EnrollmentRepository extends IRepository
{
    public function __construct(
        protected readonly EducationEnrollment $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('enrolled_at')->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationEnrollment
    {
        $enrollment = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $enrollment instanceof EducationEnrollment ? $enrollment : null;
    }

    public function lockScoped(int $id, EducationUserContext $context): ?EducationEnrollment
    {
        $enrollment = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->lockForUpdate()
            ->first();

        return $enrollment instanceof EducationEnrollment ? $enrollment : null;
    }

    public function createPending(array $data): EducationEnrollment
    {
        return $this->create($data);
    }

    public function markConfirmed(int $id, int $accountId, ?int $operatorId): EducationEnrollment
    {
        $enrollment = $this->findById($id);
        if (! $enrollment instanceof EducationEnrollment) {
            throw new \RuntimeException('Enrollment not found.');
        }

        $now = Carbon::now()->toDateTimeString();
        $enrollment->fill([
            'account_id' => $accountId,
            'status' => 'confirmed',
            'confirmed_at' => $enrollment->confirmed_at ?? $now,
            'materialized_at' => $enrollment->materialized_at ?? $now,
            'updated_by' => $operatorId,
        ]);
        $enrollment->save();

        return $enrollment->refresh();
    }

    public function cancel(int $id, string $reason, ?int $operatorId): EducationEnrollment
    {
        $enrollment = $this->findById($id);
        if (! $enrollment instanceof EducationEnrollment) {
            throw new \RuntimeException('Enrollment not found.');
        }

        $enrollment->fill([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now()->toDateTimeString(),
            'cancel_reason' => $reason,
            'updated_by' => $operatorId,
        ]);
        $enrollment->save();

        return $enrollment->refresh();
    }

    public function nextEnrollmentNo(int $tenantId, int $campusId): string
    {
        $tenantPart = str_pad((string) ($tenantId % 10000), 4, '0', \STR_PAD_LEFT);
        $campusPart = str_pad((string) ($campusId % 1000), 3, '0', \STR_PAD_LEFT);

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $number = 'ENR' . Carbon::now()->format('YmdHis') . $tenantPart . $campusPart . random_int(1000, 9999);
            if (! $this->getQuery()->where('tenant_id', $tenantId)->where('enrollment_no', $number)->exists()) {
                return $number;
            }
        }

        return 'ENR' . Carbon::now()->format('YmdHisv') . $tenantPart . $campusPart . random_int(1000, 9999);
    }

    public function accountLedgerRows(int $accountId, array $filters, EducationUserContext $context): array
    {
        $accountRepository = make(StudentCourseAccountRepository::class);

        return $accountRepository->ledger($accountId, $filters, $context);
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        if ($context->platformAccess) {
            if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            }
            if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
                $query->where('campus_id', (int) $filters['campus_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->where('tenant_id', $context->tenantId);
        $campusId = isset($filters['campus_id']) && $filters['campus_id'] !== '' ? (int) $filters['campus_id'] : null;

        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            if ($campusId !== null) {
                $query->where('campus_id', $campusId);
            }

            return $query;
        }

        if ($campusId !== null) {
            $context->canAccessCampus($campusId)
                ? $query->where('campus_id', $campusId)
                : $query->whereRaw('1 = 0');

            return $query;
        }

        $context->campusIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('campus_id', $context->campusIds);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach (['student_id', 'course_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, (int) $filters[$column]);
            }
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['enrolled_at_start']) && $filters['enrolled_at_start'] !== '') {
            $query->where('enrolled_at', '>=', $filters['enrolled_at_start'] . ' 00:00:00');
        }
        if (isset($filters['enrolled_at_end']) && $filters['enrolled_at_end'] !== '') {
            $query->where('enrolled_at', '<=', $filters['enrolled_at_end'] . ' 23:59:59');
        }
        if (! isset($filters['keyword']) || $filters['keyword'] === '') {
            return;
        }

        $keyword = '%' . $filters['keyword'] . '%';
        $query->where(static function (Builder $query) use ($keyword): void {
            $query->where('enrollment_no', 'like', $keyword)
                ->orWhere('student_name_snapshot', 'like', $keyword)
                ->orWhere('course_name_snapshot', 'like', $keyword)
                ->orWhere('package_name_snapshot', 'like', $keyword);
        });
    }
}

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

use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationStudentCourseAccount>
 */
final class StudentCourseAccountRepository extends IRepository
{
    public function __construct(
        protected readonly EducationStudentCourseAccount $model
    ) {}

    public function pageByContext(array $filters, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $filters);
        $this->applyFilters($query, $filters);
        $query->orderByDesc('updated_at')->orderByDesc('id');

        $result = $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));

        return $this->hydrateNames($result);
    }

    public function findScoped(int $id, EducationUserContext $context): ?EducationStudentCourseAccount
    {
        $account = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $account instanceof EducationStudentCourseAccount ? $account : null;
    }

    public function findByStudentCourseForUpdate(int $tenantId, int $campusId, int $studentId, int $courseId): ?EducationStudentCourseAccount
    {
        $account = $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->lockForUpdate()
            ->first();

        return $account instanceof EducationStudentCourseAccount ? $account : null;
    }

    public function createForEnrollment(array $data): EducationStudentCourseAccount
    {
        return $this->create($data);
    }

    public function updateBalances(int $accountId, array $delta, ?int $operatorId): EducationStudentCourseAccount
    {
        $account = $this->getQuery()->whereKey($accountId)->lockForUpdate()->first();
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new \RuntimeException('Student course account not found.');
        }

        foreach ($delta as $column => $value) {
            $account->{$column} = $value;
        }
        $account->updated_by = $operatorId;
        $account->save();

        return $account->refresh();
    }

    public function ledger(int $accountId, array $filters, EducationUserContext $context): array
    {
        $account = $this->findScoped($accountId, $context);
        if (! $account instanceof EducationStudentCourseAccount) {
            return ['list' => [], 'total' => 0];
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = [];
        $enrollments = EducationEnrollment::query()
            ->where('tenant_id', $account->tenant_id)
            ->where('account_id', $accountId)
            ->whereIn('status', ['confirmed', 'cancelled'])
            ->orderByDesc('id')
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->materialized_at !== null) {
                $rows[] = [
                    'source_type' => 'enrollment',
                    'source_id' => (int) $enrollment->id,
                    'source_no' => $enrollment->enrollment_no,
                    'occurred_at' => $enrollment->materialized_at?->toDateTimeString(),
                    'direction' => 'increase',
                    'units' => $enrollment->total_units,
                    'before_available_units' => null,
                    'after_available_units' => null,
                    'operator_id' => $enrollment->updated_by,
                    'operator_name' => null,
                    'remark' => $enrollment->remark,
                ];
            }
            if ($enrollment->status === 'cancelled' && $enrollment->cancelled_at !== null) {
                $rows[] = [
                    'source_type' => 'enrollment_cancel',
                    'source_id' => (int) $enrollment->id,
                    'source_no' => $enrollment->enrollment_no,
                    'occurred_at' => $enrollment->cancelled_at?->toDateTimeString(),
                    'direction' => 'decrease',
                    'units' => $enrollment->total_units,
                    'before_available_units' => null,
                    'after_available_units' => null,
                    'operator_id' => $enrollment->updated_by,
                    'operator_name' => null,
                    'remark' => $enrollment->cancel_reason,
                ];
            }
        }

        if (isset($filters['source_type']) && $filters['source_type'] !== '') {
            $filteredRows = [];
            foreach ($rows as $row) {
                if ($row['source_type'] === $filters['source_type']) {
                    $filteredRows[] = $row;
                }
            }
            $rows = $filteredRows;
        }

        $rowCount = \count($rows);
        for ($leftIndex = 0; $leftIndex < $rowCount; ++$leftIndex) {
            for ($rightIndex = $leftIndex + 1; $rightIndex < $rowCount; ++$rightIndex) {
                $timeCompare = strcmp((string) $rows[$rightIndex]['occurred_at'], (string) $rows[$leftIndex]['occurred_at']);
                $rightIsNewer = $timeCompare > 0
                    || ($timeCompare === 0 && $rows[$rightIndex]['source_type'] === 'enrollment_cancel' && $rows[$leftIndex]['source_type'] !== 'enrollment_cancel');
                if (! $rightIsNewer) {
                    continue;
                }

                $current = $rows[$leftIndex];
                $rows[$leftIndex] = $rows[$rightIndex];
                $rows[$rightIndex] = $current;
            }
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? $filters['page_size'] ?? 15)));
        $total = \count($rows);
        $offset = ($page - 1) * $pageSize;

        return [
            'list' => \array_slice($rows, $offset, $pageSize),
            'total' => $total,
        ];
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, $filters, $context);

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
    }

    private function hydrateNames(array $result): array
    {
        $studentIds = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['student_id'], $result['list'])));
        $courseIds = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['course_id'], $result['list'])));
        $students = $studentIds === [] ? [] : EducationStudent::query()->whereIn('id', $studentIds)->get()->keyBy('id')->all();
        $courses = $courseIds === [] ? [] : EducationCourse::query()->whereIn('id', $courseIds)->get()->keyBy('id')->all();

        foreach ($result['list'] as &$row) {
            $student = $students[$row['student_id']] ?? null;
            $course = $courses[$row['course_id']] ?? null;
            $row['student_name'] = $student?->name;
            $row['student_no'] = $student?->student_no;
            $row['course_name'] = $course?->name;
        }
        unset($row);

        return $result;
    }
}

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

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class AcademicReportRepository
{
    public function dashboard(array $params, EducationUserContext $context): array
    {
        $startAt = (string) $params['start_at'];
        $endAt = (string) $params['end_at'];

        return [
            'range' => ['start_at' => $startAt, 'end_at' => $endAt],
            'campus_id' => isset($params['campus_id']) && $params['campus_id'] !== '' ? (int) $params['campus_id'] : null,
            'metrics' => [
                'student_count' => $this->countRows('edu_students', $context, $params),
                'active_student_count' => $this->countRows('edu_students', $context, $params, ['status' => 'enabled']),
                'guardian_count' => $this->countRows('edu_guardians', $context, $params, campusScoped: false),
                'teacher_count' => $this->countRows('edu_teachers', $context, $params),
                'active_class_count' => $this->countRows('edu_classes', $context, $params, ['status' => 'enabled']),
                'scheduled_lesson_count' => $this->countRows('edu_lessons', $context, $params, ['status' => 'scheduled'], 'start_at', $startAt, $endAt),
                'completed_lesson_count' => $this->countRows('edu_lessons', $context, $params, ['status' => 'completed'], 'start_at', $startAt, $endAt),
                'cancelled_lesson_count' => $this->countRows('edu_lessons', $context, $params, ['status' => 'cancelled'], 'start_at', $startAt, $endAt),
                'attendance_count' => $this->countRows('edu_lesson_attendances', $context, $params, dateColumn: 'submitted_at', startAt: $startAt, endAt: $endAt),
                'present_count' => $this->countRows('edu_lesson_attendances', $context, $params, ['attendance_status' => 'present'], 'submitted_at', $startAt, $endAt),
                'late_count' => $this->countRows('edu_lesson_attendances', $context, $params, ['attendance_status' => 'late'], 'submitted_at', $startAt, $endAt),
                'absent_count' => $this->countRows('edu_lesson_attendances', $context, $params, ['attendance_status' => 'absent'], 'submitted_at', $startAt, $endAt),
                'leave_count' => $this->countRows('edu_lesson_attendances', $context, $params, ['attendance_status' => 'leave'], 'submitted_at', $startAt, $endAt),
                ...$this->dashboardConsumptionMetrics($params, $context, $startAt, $endAt),
                ...$this->dashboardAccountMetrics($params, $context),
                'pending_leave_count' => $this->countRows('edu_leave_requests', $context, $params, ['status' => 'pending'], 'requested_at', $startAt, $endAt),
                'approved_leave_count' => $this->countRows('edu_leave_requests', $context, $params, ['status' => 'approved'], 'requested_at', $startAt, $endAt),
                'makeup_scheduled_count' => $this->countRows('edu_leave_requests', $context, $params, ['status' => 'makeup_scheduled'], 'requested_at', $startAt, $endAt),
                'published_notice_count' => $this->countRows('edu_notices', $context, $params, ['status' => 'published'], 'published_at', $startAt, $endAt),
                'unread_notice_receipt_count' => $this->noticeReceiptCount($params, $context, 'unread'),
            ],
            'trends' => $this->dashboardTrends($params, $context, $startAt, $endAt),
            'alerts' => $this->dashboardAlerts($params, $context),
        ];
    }

    public function attendanceRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->attendanceBaseQuery($params, $context)
            ->select([
                Db::raw('DATE(a.submitted_at) as date'),
                'a.campus_id',
                'campuses.name as campus_name',
                'a.class_id',
                'l.class_name_snapshot as class_name',
                'l.teacher_id',
                'l.teacher_name_snapshot as teacher_name',
                'a.course_id',
                'l.course_name_snapshot as course_name',
                'a.lesson_id',
                'l.title as lesson_title',
                'a.student_id',
                'ls.student_name_snapshot as student_name',
                'a.attendance_status',
                'a.consume_policy',
                'a.consumed_units',
                'a.submitted_at',
            ])
            ->orderByDesc('a.submitted_at')
            ->orderByDesc('a.id');

        return $this->paginate($query, $page, $pageSize);
    }

    public function attendanceSummary(array $params, EducationUserContext $context): array
    {
        $rows = $this->attendanceBaseQuery($params, $context)
            ->selectRaw('attendance_status, COUNT(*) as total')
            ->groupBy('attendance_status')
            ->get();
        $counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'leave' => 0];
        foreach ($rows as $row) {
            $counts[(string) $row->attendance_status] = (int) $row->total;
        }
        $total = array_sum($counts);
        $attended = $counts['present'] + $counts['late'];

        return [
            'total_records' => $total,
            'present_count' => $counts['present'],
            'late_count' => $counts['late'],
            'absent_count' => $counts['absent'],
            'leave_count' => $counts['leave'],
            'attendance_rate' => $total > 0 ? $this->decimal($attended * 100 / $total) : '0.00',
            'leave_rate' => $total > 0 ? $this->decimal($counts['leave'] * 100 / $total) : '0.00',
        ];
    }

    public function consumptionRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->consumptionBaseQuery($params, $context)
            ->select([
                Db::raw('DATE(c.created_at) as date'),
                'c.campus_id',
                'campuses.name as campus_name',
                'c.consumption_no',
                'c.account_id',
                'c.student_id',
                'students.name as student_name',
                'c.course_id',
                'courses.name as course_name',
                'lessons.class_id',
                'lessons.class_name_snapshot as class_name',
                'lessons.teacher_id',
                'lessons.teacher_name_snapshot as teacher_name',
                'c.lesson_id',
                'lessons.title as lesson_title',
                'c.source_type',
                'c.direction',
                'c.units',
                'c.before_available_units',
                'c.after_available_units',
                'c.status',
                'c.created_at',
            ])
            ->orderByDesc('c.created_at')
            ->orderByDesc('c.id');

        return $this->paginate($query, $page, $pageSize);
    }

    public function consumptionSummary(array $params, EducationUserContext $context): array
    {
        $rows = $this->consumptionBaseQuery($params, $context)
            ->selectRaw(
                "SUM(CASE WHEN c.direction = 'decrease' AND c.status = 'active' THEN c.units ELSE 0 END) as decrease_units,
                SUM(CASE WHEN c.direction = 'increase' AND c.status = 'active' THEN c.units ELSE 0 END) as rollback_units,
                SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END) as active_row_count,
                SUM(CASE WHEN c.status = 'reversed' THEN 1 ELSE 0 END) as reversed_row_count"
            )
            ->first();
        $decrease = (float) ($rows->decrease_units ?? 0);
        $rollback = (float) ($rows->rollback_units ?? 0);

        return [
            'decrease_units' => $this->decimal($decrease),
            'rollback_units' => $this->decimal($rollback),
            'net_units' => $this->decimal($decrease - $rollback),
            'active_row_count' => (int) ($rows->active_row_count ?? 0),
            'reversed_row_count' => (int) ($rows->reversed_row_count ?? 0),
        ];
    }

    public function accountBalanceRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->accountBalanceBaseQuery($params, $context)
            ->select([
                'a.id as account_id',
                'a.campus_id',
                'campuses.name as campus_name',
                'a.student_id',
                'students.name as student_name',
                'students.student_no',
                'a.course_id',
                'courses.name as course_name',
                'a.purchased_units',
                'a.bonus_units',
                'a.consumed_units',
                'a.adjusted_units',
                'a.refunded_units',
                'a.frozen_units',
                'a.available_units',
                'a.status',
                'a.opened_at',
                'a.expires_at',
            ])
            ->orderBy('a.available_units')
            ->orderByDesc('a.id');
        $pageResult = $this->paginate($query, $page, $pageSize);
        $pageResult['list'] = array_values(array_filter(array_map(function (array $row) use ($params): ?array {
            $row['balance_level'] = $this->balanceLevel($row);
            if (isset($params['balance_level']) && $params['balance_level'] !== '' && $params['balance_level'] !== $row['balance_level']) {
                return null;
            }

            return $row;
        }, $pageResult['list'])));
        if (isset($params['balance_level']) && $params['balance_level'] !== '') {
            $allRows = $this->accountBalanceRowsForLevel($params, $context);
            $pageResult['total'] = \count($allRows);
            $pageResult['list'] = \array_slice($allRows, ($page - 1) * $pageSize, $pageSize);
        }

        return $pageResult;
    }

    public function accountBalanceSummary(array $params, EducationUserContext $context): array
    {
        $rows = $this->accountBalanceBaseQuery($params, $context)
            ->selectRaw(
                "COUNT(*) as account_count,
                SUM(CASE WHEN a.status = 'active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN a.status = 'frozen' THEN 1 ELSE 0 END) as frozen_count,
                SUM(CASE WHEN a.status = 'closed' THEN 1 ELSE 0 END) as closed_count,
                SUM(a.purchased_units) as total_purchased_units,
                SUM(a.bonus_units) as total_bonus_units,
                SUM(a.consumed_units) as total_consumed_units,
                SUM(a.adjusted_units) as total_adjusted_units,
                SUM(a.refunded_units) as total_refunded_units,
                SUM(a.frozen_units) as total_frozen_units,
                SUM(a.available_units) as total_available_units"
            )
            ->first();
        $levelRows = $this->accountBalanceRowsForLevel($params, $context);

        return [
            'account_count' => (int) ($rows->account_count ?? 0),
            'active_count' => (int) ($rows->active_count ?? 0),
            'frozen_count' => (int) ($rows->frozen_count ?? 0),
            'closed_count' => (int) ($rows->closed_count ?? 0),
            'total_purchased_units' => $this->decimal($rows->total_purchased_units ?? 0),
            'total_bonus_units' => $this->decimal($rows->total_bonus_units ?? 0),
            'total_consumed_units' => $this->decimal($rows->total_consumed_units ?? 0),
            'total_adjusted_units' => $this->decimal($rows->total_adjusted_units ?? 0),
            'total_refunded_units' => $this->decimal($rows->total_refunded_units ?? 0),
            'total_frozen_units' => $this->decimal($rows->total_frozen_units ?? 0),
            'total_available_units' => $this->decimal($rows->total_available_units ?? 0),
            'low_balance_count' => \count(array_filter($levelRows, static fn (array $row): bool => $row['balance_level'] === 'low' || $row['balance_level'] === 'zero')),
            'expiring_count' => \count(array_filter($levelRows, static fn (array $row): bool => $row['balance_level'] === 'expiring_soon' || $row['balance_level'] === 'expired')),
        ];
    }

    public function leaveRows(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->leaveBaseQuery($params, $context)
            ->select([
                'lr.id as leave_id',
                'lr.leave_no',
                'lr.source',
                'lr.leave_type',
                'lr.status',
                'lr.campus_id',
                'campuses.name as campus_name',
                'lr.class_id',
                'lessons.class_name_snapshot as class_name',
                'lr.teacher_id',
                'lessons.teacher_name_snapshot as teacher_name',
                'lr.course_id',
                'lessons.course_name_snapshot as course_name',
                'lr.student_id',
                'students.name as student_name',
                'lr.lesson_id',
                'lessons.title as lesson_title',
                'lr.requested_at',
                'lr.reviewed_at',
                'lr.makeup_required',
            ])
            ->orderByDesc('lr.requested_at')
            ->orderByDesc('lr.id');

        return $this->paginate($query, $page, $pageSize);
    }

    public function leaveSummary(array $params, EducationUserContext $context): array
    {
        $rows = $this->leaveBaseQuery($params, $context)
            ->selectRaw('status, source, COUNT(*) as total')
            ->groupBy('status', 'source')
            ->get();
        $summary = [
            'total_count' => 0,
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'cancelled_count' => 0,
            'makeup_scheduled_count' => 0,
            'guardian_source_count' => 0,
            'teacher_source_count' => 0,
            'staff_source_count' => 0,
        ];
        foreach ($rows as $row) {
            $total = (int) $row->total;
            $summary['total_count'] += $total;
            $statusKey = (string) $row->status . '_count';
            $sourceKey = (string) $row->source . '_source_count';
            if (array_key_exists($statusKey, $summary)) {
                $summary[$statusKey] += $total;
            }
            if (array_key_exists($sourceKey, $summary)) {
                $summary[$sourceKey] += $total;
            }
        }

        return $summary;
    }

    private function dashboardConsumptionMetrics(array $params, EducationUserContext $context, string $startAt, string $endAt): array
    {
        $summary = $this->consumptionSummary([...$params, 'start_at' => $startAt, 'end_at' => $endAt], $context);

        return [
            'consumed_units' => $summary['decrease_units'],
            'rollback_units' => $summary['rollback_units'],
            'net_consumed_units' => $summary['net_units'],
        ];
    }

    private function dashboardAccountMetrics(array $params, EducationUserContext $context): array
    {
        $summary = $this->accountBalanceSummary($params, $context);

        return [
            'total_available_units' => $summary['total_available_units'],
            'frozen_units' => $summary['total_frozen_units'],
            'low_balance_account_count' => $summary['low_balance_count'],
            'expiring_account_count' => $summary['expiring_count'],
        ];
    }

    private function dashboardTrends(array $params, EducationUserContext $context, string $startAt, string $endAt): array
    {
        $lessonRows = $this->baseQuery('edu_lessons', $context, $params)
            ->whereBetween('start_at', [$startAt, $endAt])
            ->selectRaw("DATE(start_at) as date, SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_lesson_count, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lesson_count")
            ->groupBy(Db::raw('DATE(start_at)'))
            ->get();
        $consumptionRows = $this->baseQuery('edu_lesson_consumptions', $context, $params)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->where('status', 'active')
            ->where('direction', 'decrease')
            ->selectRaw('DATE(created_at) as date, SUM(units) as consumed_units')
            ->groupBy(Db::raw('DATE(created_at)'))
            ->get();
        $trends = [];
        foreach ($lessonRows as $row) {
            $trends[(string) $row->date] = [
                'date' => (string) $row->date,
                'scheduled_lesson_count' => (int) $row->scheduled_lesson_count,
                'completed_lesson_count' => (int) $row->completed_lesson_count,
                'consumed_units' => '0.00',
            ];
        }
        foreach ($consumptionRows as $row) {
            $date = (string) $row->date;
            $trends[$date] ??= ['date' => $date, 'scheduled_lesson_count' => 0, 'completed_lesson_count' => 0, 'consumed_units' => '0.00'];
            $trends[$date]['consumed_units'] = $this->decimal($row->consumed_units ?? 0);
        }
        ksort($trends);

        return array_values($trends);
    }

    private function dashboardAlerts(array $params, EducationUserContext $context): array
    {
        $summary = $this->accountBalanceSummary($params, $context);

        return array_values(array_filter([
            $summary['low_balance_count'] > 0 ? ['type' => 'low_balance', 'level' => 'warning', 'title' => 'Low balance accounts', 'count' => $summary['low_balance_count']] : null,
            $summary['expiring_count'] > 0 ? ['type' => 'expiring_account', 'level' => 'warning', 'title' => 'Expiring accounts', 'count' => $summary['expiring_count']] : null,
        ]));
    }

    private function attendanceBaseQuery(array $params, EducationUserContext $context): mixed
    {
        $query = Db::table('edu_lesson_attendances as a')
            ->leftJoin('edu_lessons as l', 'l.id', '=', 'a.lesson_id')
            ->leftJoin('edu_lesson_students as ls', 'ls.id', '=', 'a.lesson_student_id')
            ->leftJoin('edu_campuses as campuses', 'campuses.id', '=', 'a.campus_id')
            ->whereNull('a.deleted_at');
        $this->applyTenantAndCampus($query, $context, $params, 'a');
        $this->whereDateRange($query, 'a.submitted_at', $params);
        foreach (['class_id', 'teacher_id', 'course_id'] as $column) {
            $this->whereInt($query, 'a.' . $column, $params[$column] ?? null);
        }
        if (isset($params['attendance_status']) && $params['attendance_status'] !== '') {
            $query->where('a.attendance_status', $params['attendance_status']);
        }

        return $query;
    }

    private function consumptionBaseQuery(array $params, EducationUserContext $context): mixed
    {
        $query = Db::table('edu_lesson_consumptions as c')
            ->leftJoin('edu_students as students', 'students.id', '=', 'c.student_id')
            ->leftJoin('edu_courses as courses', 'courses.id', '=', 'c.course_id')
            ->leftJoin('edu_lessons as lessons', 'lessons.id', '=', 'c.lesson_id')
            ->leftJoin('edu_campuses as campuses', 'campuses.id', '=', 'c.campus_id')
            ->whereNull('c.deleted_at');
        $this->applyTenantAndCampus($query, $context, $params, 'c');
        $this->whereDateRange($query, 'c.created_at', $params);
        foreach (['course_id', 'class_id', 'student_id', 'account_id'] as $column) {
            $tableColumn = $column === 'class_id' ? 'lessons.class_id' : 'c.' . $column;
            $this->whereInt($query, $tableColumn, $params[$column] ?? null);
        }
        foreach (['source_type', 'status'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where('c.' . $column, $params[$column]);
            }
        }

        return $query;
    }

    private function accountBalanceBaseQuery(array $params, EducationUserContext $context): mixed
    {
        $query = Db::table('edu_student_course_accounts as a')
            ->leftJoin('edu_students as students', 'students.id', '=', 'a.student_id')
            ->leftJoin('edu_courses as courses', 'courses.id', '=', 'a.course_id')
            ->leftJoin('edu_campuses as campuses', 'campuses.id', '=', 'a.campus_id')
            ->whereNull('a.deleted_at');
        $this->applyTenantAndCampus($query, $context, $params, 'a');
        foreach (['course_id', 'student_id'] as $column) {
            $this->whereInt($query, 'a.' . $column, $params[$column] ?? null);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('a.status', $params['status']);
        }

        return $query;
    }

    private function leaveBaseQuery(array $params, EducationUserContext $context): mixed
    {
        $query = Db::table('edu_leave_requests as lr')
            ->leftJoin('edu_lessons as lessons', 'lessons.id', '=', 'lr.lesson_id')
            ->leftJoin('edu_students as students', 'students.id', '=', 'lr.student_id')
            ->leftJoin('edu_campuses as campuses', 'campuses.id', '=', 'lr.campus_id')
            ->whereNull('lr.deleted_at');
        $this->applyTenantAndCampus($query, $context, $params, 'lr');
        $this->whereDateRange($query, 'lr.requested_at', $params);
        foreach (['class_id', 'teacher_id', 'course_id'] as $column) {
            $this->whereInt($query, 'lr.' . $column, $params[$column] ?? null);
        }
        foreach (['source', 'leave_type', 'status'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where('lr.' . $column, $params[$column]);
            }
        }

        return $query;
    }

    private function baseQuery(string $table, EducationUserContext $context, array $params, bool $campusScoped = true): mixed
    {
        $query = Db::table($table)->whereNull('deleted_at');
        $this->applyTenantAndCampus($query, $context, $params, null, $campusScoped);

        return $query;
    }

    private function countRows(
        string $table,
        EducationUserContext $context,
        array $params,
        array $equals = [],
        ?string $dateColumn = null,
        ?string $startAt = null,
        ?string $endAt = null,
        bool $campusScoped = true
    ): int {
        $query = $this->baseQuery($table, $context, $params, $campusScoped);
        foreach ($equals as $column => $value) {
            $query->where($column, $value);
        }
        if ($dateColumn !== null && $startAt !== null && $endAt !== null) {
            $query->whereBetween($dateColumn, [$startAt, $endAt]);
        }

        return (int) $query->count();
    }

    private function noticeReceiptCount(array $params, EducationUserContext $context, string $status): int
    {
        $query = Db::table('edu_notice_receipts as r')
            ->join('edu_notices as n', 'n.id', '=', 'r.notice_id')
            ->whereNull('r.deleted_at')
            ->whereNull('n.deleted_at')
            ->where('r.status', $status);
        $this->applyTenantAndCampus($query, $context, $params, 'n');

        return (int) $query->count();
    }

    private function accountBalanceRowsForLevel(array $params, EducationUserContext $context): array
    {
        $rows = $this->accountBalanceBaseQuery($params, $context)
            ->select([
                'a.id as account_id',
                'a.campus_id',
                'campuses.name as campus_name',
                'a.student_id',
                'students.name as student_name',
                'students.student_no',
                'a.course_id',
                'courses.name as course_name',
                'a.purchased_units',
                'a.bonus_units',
                'a.consumed_units',
                'a.adjusted_units',
                'a.refunded_units',
                'a.frozen_units',
                'a.available_units',
                'a.status',
                'a.opened_at',
                'a.expires_at',
            ])
            ->orderBy('a.available_units')
            ->orderByDesc('a.id')
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->map(function (array $row): array {
                $row['balance_level'] = $this->balanceLevel($row);

                return $row;
            })
            ->all();

        if (isset($params['balance_level']) && $params['balance_level'] !== '') {
            return array_values(array_filter($rows, static fn (array $row): bool => $row['balance_level'] === $params['balance_level']));
        }

        return array_values($rows);
    }

    private function applyTenantAndCampus(mixed $query, EducationUserContext $context, array $params, ?string $alias = null, bool $campusScoped = true): void
    {
        $tenantColumn = $alias === null ? 'tenant_id' : $alias . '.tenant_id';
        $campusColumn = $alias === null ? 'campus_id' : $alias . '.campus_id';
        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return;
        }
        $query->where($tenantColumn, $context->tenantId);
        if (! $campusScoped) {
            return;
        }
        $campusId = isset($params['campus_id']) && $params['campus_id'] !== '' ? (int) $params['campus_id'] : null;
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            if ($campusId !== null) {
                $query->where($campusColumn, $campusId);
            }

            return;
        }
        if ($campusId !== null) {
            $context->canAccessCampus($campusId) ? $query->where($campusColumn, $campusId) : $query->whereRaw('1 = 0');

            return;
        }
        $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn($campusColumn, $context->campusIds);
    }

    private function whereDateRange(mixed $query, string $column, array $params): void
    {
        if (isset($params['start_at'], $params['end_at']) && $params['start_at'] !== '' && $params['end_at'] !== '') {
            $query->whereBetween($column, [(string) $params['start_at'], (string) $params['end_at']]);
        }
    }

    private function whereInt(mixed $query, string $column, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $query->where($column, (int) $value);
        }
    }

    private function paginate(mixed $query, int $page, int $pageSize): array
    {
        $total = (int) (clone $query)->count();
        $list = (clone $query)
            ->forPage($page, $pageSize)
            ->get()
            ->map(static fn (object $row): array => (array) $row)
            ->all();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
        ];
    }

    private function balanceLevel(array $row): string
    {
        $available = (float) ($row['available_units'] ?? 0);
        $expiresAt = $row['expires_at'] ?? null;
        if ($expiresAt !== null && Carbon::parse((string) $expiresAt)->isPast()) {
            return 'expired';
        }
        if ($available <= 0.0) {
            return 'zero';
        }
        if ($expiresAt !== null && Carbon::parse((string) $expiresAt)->diffInDays(Carbon::now(), false) >= -30) {
            return 'expiring_soon';
        }
        if ($available < 5.0) {
            return 'low';
        }

        return 'normal';
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

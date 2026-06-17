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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Repository\Education\Academic\AcademicReportRepository;
use App\Schema\Education\Academic\AcademicDashboardSchema;
use App\Schema\Education\Academic\AccountBalanceReportSchema;
use App\Schema\Education\Academic\AttendanceReportSchema;
use App\Schema\Education\Academic\ConsumptionReportSchema;
use App\Schema\Education\Academic\LeaveReportSchema;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class AcademicReportService
{
    private const MAX_REPORT_DAYS = 366;

    public function __construct(private readonly AcademicReportRepository $repository) {}

    public function dashboard(array $params, EducationUserContext $context): array
    {
        $params = $this->normalizeDateRange($params, false);
        $this->assertCampusInScope($params, $context);

        return (new AcademicDashboardSchema($this->repository->dashboard($params, $context)))->jsonSerialize();
    }

    public function attendance(array $params, EducationUserContext $context): array
    {
        $params = $this->normalizeDateRange($params, true);
        $this->assertCampusInScope($params, $context);
        $page = $this->page($params);
        $pageSize = $this->pageSize($params);
        $rows = $this->repository->attendanceRows($params, $page, $pageSize, $context);
        $rows['summary'] = $this->repository->attendanceSummary($params, $context);

        return (new AttendanceReportSchema($rows))->jsonSerialize();
    }

    public function consumption(array $params, EducationUserContext $context): array
    {
        $params = $this->normalizeDateRange($params, true);
        $this->assertCampusInScope($params, $context);
        $page = $this->page($params);
        $pageSize = $this->pageSize($params);
        $rows = $this->repository->consumptionRows($params, $page, $pageSize, $context);
        $rows['summary'] = $this->repository->consumptionSummary($params, $context);

        return (new ConsumptionReportSchema($rows))->jsonSerialize();
    }

    public function accountBalances(array $params, EducationUserContext $context): array
    {
        $this->assertCampusInScope($params, $context);
        $page = $this->page($params);
        $pageSize = $this->pageSize($params);
        $rows = $this->repository->accountBalanceRows($params, $page, $pageSize, $context);
        $rows['summary'] = $this->repository->accountBalanceSummary($params, $context);

        return (new AccountBalanceReportSchema($rows))->jsonSerialize();
    }

    public function leaves(array $params, EducationUserContext $context): array
    {
        $params = $this->normalizeDateRange($params, true);
        $this->assertCampusInScope($params, $context);
        $page = $this->page($params);
        $pageSize = $this->pageSize($params);
        $rows = $this->repository->leaveRows($params, $page, $pageSize, $context);
        $rows['summary'] = $this->repository->leaveSummary($params, $context);

        return (new LeaveReportSchema($rows))->jsonSerialize();
    }

    private function normalizeDateRange(array $params, bool $required): array
    {
        if (! isset($params['start_at'], $params['end_at']) || $params['start_at'] === '' || $params['end_at'] === '') {
            if ($required) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'start_at is required', ['field' => 'start_at']);
            }
            $now = Carbon::now();
            $params['start_at'] = $now->copy()->startOfDay()->toDateTimeString();
            $params['end_at'] = $now->copy()->endOfDay()->toDateTimeString();
        }

        $startAt = Carbon::parse((string) $params['start_at']);
        $endAt = Carbon::parse((string) $params['end_at']);
        if ($endAt->lessThanOrEqualTo($startAt)) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'end_at must be after start_at', ['field' => 'end_at']);
        }
        if ($startAt->diffInDays($endAt) > self::MAX_REPORT_DAYS) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'date range cannot exceed 366 days', ['max_days' => self::MAX_REPORT_DAYS]);
        }
        $params['start_at'] = $startAt->toDateTimeString();
        $params['end_at'] = $endAt->toDateTimeString();

        return $params;
    }

    private function assertCampusInScope(array $params, EducationUserContext $context): void
    {
        if (! isset($params['campus_id']) || $params['campus_id'] === '') {
            return;
        }
        $campusId = (int) $params['campus_id'];
        if ($context->roleCode === EducationRoleCode::TenantAdmin || $context->platformAccess) {
            return;
        }
        if (! $context->canAccessCampus($campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current scope', ['campus_id' => $campusId]);
        }
    }

    private function page(array $params): int
    {
        return max(1, (int) ($params['page'] ?? 1));
    }

    private function pageSize(array $params): int
    {
        return max(1, min(100, (int) ($params['pageSize'] ?? $params['page_size'] ?? 20)));
    }
}

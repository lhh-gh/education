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

namespace App\Http\Admin\Controller\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Academic\ReportAcceptanceRequest;
use App\Http\Admin\Request\Education\Academic\ReportAccountBalanceRequest;
use App\Http\Admin\Request\Education\Academic\ReportAttendanceRequest;
use App\Http\Admin\Request\Education\Academic\ReportConsumptionRequest;
use App\Http\Admin\Request\Education\Academic\ReportDashboardRequest;
use App\Http\Admin\Request\Education\Academic\ReportLeaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Service\Education\Academic\AcademicAcceptanceService;
use App\Service\Education\Academic\AcademicReportService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class AcademicReportController extends AbstractController
{
    public function __construct(
        private readonly AcademicReportService $reportService,
        private readonly AcademicAcceptanceService $acceptanceService
    ) {}

    #[Get(path: '/admin/education/academic/reports/dashboard', operationId: 'educationAcademicReportDashboard', summary: 'Academic report dashboard', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:dashboard')]
    public function dashboard(ReportDashboardRequest $request): Result
    {
        return $this->success($this->reportService->dashboard($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/reports/attendance', operationId: 'educationAcademicReportAttendance', summary: 'Academic attendance report', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:attendance')]
    public function attendance(ReportAttendanceRequest $request): Result
    {
        return $this->success($this->reportService->attendance($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/reports/consumption', operationId: 'educationAcademicReportConsumption', summary: 'Academic consumption report', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:consumption')]
    public function consumption(ReportConsumptionRequest $request): Result
    {
        return $this->success($this->reportService->consumption($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/reports/account-balances', operationId: 'educationAcademicReportAccountBalances', summary: 'Academic account balance report', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:account-balance')]
    public function accountBalances(ReportAccountBalanceRequest $request): Result
    {
        return $this->success($this->reportService->accountBalances($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/reports/leaves', operationId: 'educationAcademicReportLeaves', summary: 'Academic leave report', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:leave')]
    public function leaves(ReportLeaveRequest $request): Result
    {
        return $this->success($this->reportService->leaves($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/reports/v1-acceptance-summary', operationId: 'educationAcademicReportV1AcceptanceSummary', summary: 'V1 acceptance summary', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:report:acceptance')]
    public function acceptanceSummary(ReportAcceptanceRequest $request): Result
    {
        return $this->success($this->acceptanceService->summary($request->validated(), $this->context()));
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}

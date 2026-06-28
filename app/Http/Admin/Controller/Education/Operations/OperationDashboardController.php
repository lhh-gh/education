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

namespace App\Http\Admin\Controller\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Operations\OperationDashboardRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\OperationDashboardService;
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
final class OperationDashboardController extends AbstractController
{
    public function __construct(private readonly OperationDashboardService $service) {}

    #[Get(path: '/admin/education/operations/dashboard/overview', operationId: 'educationOperationDashboardOverview', summary: 'Operation dashboard overview', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:dashboard:overview')]
    public function overview(OperationDashboardRequest $request): Result
    {
        $params = $request->validated();

        return $this->success($this->service->overview($this->context(), isset($params['campus_id']) ? (int) $params['campus_id'] : null));
    }

    #[Get(path: '/admin/education/operations/dashboard/consumption-trend', operationId: 'educationOperationDashboardConsumptionTrend', summary: 'Operation dashboard consumption trend', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:dashboard:overview')]
    public function consumptionTrend(OperationDashboardRequest $request): Result
    {
        return $this->success($this->service->consumptionTrend($this->context(), $request->validated()));
    }

    #[Get(path: '/admin/education/operations/dashboard/renewal-alert-summary', operationId: 'educationOperationDashboardRenewalAlertSummary', summary: 'Operation dashboard renewal alert summary', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:dashboard:overview')]
    public function renewalAlertSummary(OperationDashboardRequest $request): Result
    {
        return $this->success($this->service->renewalAlertSummary($this->context(), $request->validated()));
    }

    #[Get(path: '/admin/education/operations/dashboard/daily-metrics', operationId: 'educationOperationDashboardDailyMetrics', summary: 'Operation dashboard daily metrics', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:dashboard:overview')]
    public function dailyMetrics(OperationDashboardRequest $request): Result
    {
        return $this->success($this->service->dailyMetrics($this->context(), $request->validated()));
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

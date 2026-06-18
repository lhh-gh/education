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

namespace App\Http\Admin\Controller\Education\Growth;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Growth\GrowthDashboardRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Growth\ConsultantMetricService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class ConsultantMetricController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly ConsultantMetricService $service) {}

    #[Get(path: '/admin/education/growth/consultant-metrics', operationId: 'educationGrowthConsultantMetrics', summary: 'Growth consultant metrics', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:consultant-metric:page')]
    public function page(GrowthDashboardRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $consultantUserId = (int) ($data['consultant_user_id'] ?? $context->userId);
        $metricDate = (string) ($data['metric_date'] ?? $data['start_date'] ?? date('Y-m-d'));

        return $this->success(['list' => [$this->service->aggregateDaily($this->tenantId($context), $this->campusId($context), $consultantUserId, $metricDate)]]);
    }
}

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

namespace App\Http\Admin\Controller\Education\Group;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\GroupMetricService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class GroupMetricController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly GroupMetricService $service) {}

    #[Get(path: '/admin/education/group/operation-metrics', operationId: 'educationGroupMetricPage', summary: 'Group operation metrics', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:metric:page')]
    public function page(): Result
    {
        return $this->success($this->service->page($this->getRequestData(), $this->context()));
    }

    #[Get(path: '/admin/education/group/operation-dashboard', operationId: 'educationGroupMetricDashboard', summary: 'Group operation dashboard', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:metric:page')]
    public function dashboard(): Result
    {
        return $this->success($this->service->dashboard($this->getRequestData(), $this->context()));
    }
}

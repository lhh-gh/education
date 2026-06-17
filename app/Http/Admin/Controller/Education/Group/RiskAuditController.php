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
use App\Http\Admin\Request\Education\Group\RiskAuditPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\RiskAuditService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class RiskAuditController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly RiskAuditService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/group/risk-audit-events/page', operationId: 'educationGroupRiskAuditPage', summary: 'Group risk audit page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:risk-audit:page')]
    public function page(RiskAuditPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/group/risk-audit-events/{id}/handled', operationId: 'educationGroupRiskAuditHandled', summary: 'Group risk audit handled', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:risk-audit:handle')]
    public function markHandled(int $id): Result
    {
        $context = $this->context();
        $result = $this->service->markHandled($id, $context);
        $this->audit($this->events, 'education.group.risk_audit.handled', 'risk_audit', $id, $context, $result ?? []);

        return $this->success($result);
    }
}

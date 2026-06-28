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

namespace App\Http\Admin\Controller\Education\Workflow;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Workflow\SlaPolicySaveRequest;
use App\Http\Admin\Request\Education\Workflow\WorkflowConfigPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Workflow\SlaService;
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
final class SlaPolicyController extends AbstractController
{
    use WorkflowControllerTrait;

    public function __construct(private readonly SlaService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/workflow/sla-policies/page', operationId: 'educationWorkflowSlaPolicyPage', summary: 'Workflow SLA policy page', tags: ['Education Workflow'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:sla:page')]
    public function page(WorkflowConfigPageRequest $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->pagePolicies($this->tenantId($context), $request->validated(), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/workflow/sla-policies', operationId: 'educationWorkflowSlaPolicySave', summary: 'Workflow SLA policy save', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:sla:save')]
    public function save(SlaPolicySaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->savePolicy($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.workflow.sla.saved', 'workflow_sla_policy', $result['policy_id'], $context, $result);

        return $this->success($result);
    }
}

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
use App\Http\Admin\Request\Education\Workflow\WorkflowConfigPageRequest;
use App\Http\Admin\Request\Education\Workflow\WorkflowTemplateSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Workflow\WorkflowTemplateService;
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
final class WorkflowTemplateController extends AbstractController
{
    use WorkflowControllerTrait;

    public function __construct(private readonly WorkflowTemplateService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/workflow/templates/page', operationId: 'educationWorkflowTemplatePage', summary: 'Workflow template page', tags: ['Education Workflow'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:template:page')]
    public function page(WorkflowConfigPageRequest $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->page($this->tenantId($context), $request->validated(), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/workflow/templates', operationId: 'educationWorkflowTemplateSave', summary: 'Workflow template save', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:template:save')]
    public function save(WorkflowTemplateSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.workflow.template.saved', 'workflow_template', $result['template_id'], $context, $result);

        return $this->success($result);
    }
}

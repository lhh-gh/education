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
use App\Http\Admin\Request\Education\Workflow\WorkflowTaskCommentRequest;
use App\Http\Admin\Request\Education\Workflow\WorkflowTaskCompleteRequest;
use App\Http\Admin\Request\Education\Workflow\WorkflowTaskPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Workflow\WorkflowTaskService;
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
final class WorkflowTaskController extends AbstractController
{
    use WorkflowControllerTrait;

    public function __construct(private readonly WorkflowTaskService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/workflow/tasks/page', operationId: 'educationWorkflowTaskPage', summary: 'Workflow task page', tags: ['Education Workflow'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:task:page')]
    public function page(WorkflowTaskPageRequest $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->pageTasks($this->tenantId($context), $request->validated(), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/workflow/tasks/{id}/complete', operationId: 'educationWorkflowTaskComplete', summary: 'Workflow task complete', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:task:complete')]
    public function complete(int $id, WorkflowTaskCompleteRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->completeTask($id, $context->userId, (string) $data['result'], (string) $data['content']);
        $this->audit($this->events, 'education.workflow.task.completed', 'workflow_task', $id, $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/workflow/tasks/{id}/comments', operationId: 'educationWorkflowTaskComment', summary: 'Workflow task comment', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:task:comment')]
    public function comment(int $id, WorkflowTaskCommentRequest $request): Result
    {
        $context = $this->context();
        $this->service->addComment($id, $context->userId, (string) $request->validated()['content']);
        $result = ['task_id' => $id, 'commented' => true];
        $this->audit($this->events, 'education.workflow.task.commented', 'workflow_task', $id, $context, $result);

        return $this->success($result);
    }
}

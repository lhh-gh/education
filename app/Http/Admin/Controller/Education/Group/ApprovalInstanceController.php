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
use App\Http\Admin\Request\Education\Group\ApprovalInstanceCreateRequest;
use App\Http\Admin\Request\Education\Group\ApprovalTaskCompleteRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\ApprovalInstanceService;
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
final class ApprovalInstanceController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly ApprovalInstanceService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/group/approval-instances', operationId: 'educationGroupApprovalInstanceCreate', summary: 'Group approval instance create', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:approval:create')]
    public function create(ApprovalInstanceCreateRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->create($request->validated(), $context);
        $this->audit($this->events, 'education.group.approval_instance.created', 'approval_instance', $result['approval_instance_id'], $context, $result);

        return $this->success($result);
    }

    #[Get(path: '/admin/education/group/approval-tasks/page', operationId: 'educationGroupApprovalTaskPage', summary: 'Group approval task page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:approval-task:page')]
    public function pageTasks(): Result
    {
        return $this->success($this->service->pageTasks($this->getRequestData(), $this->context()));
    }

    #[Post(path: '/admin/education/group/approval-tasks/{id}/complete', operationId: 'educationGroupApprovalTaskComplete', summary: 'Group approval task complete', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:approval:complete')]
    public function completeTask(int $id, ApprovalTaskCompleteRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->completeTask($id, $request->validated(), $context);
        $this->audit($this->events, 'education.group.approval_task.completed', 'approval_task', $result['task_id'], $context, $result);

        return $this->success($result);
    }
}

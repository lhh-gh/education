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

namespace App\Http\Api\Controller\Education\Workflow;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Workflow\MobileTaskCommentRequest;
use App\Http\Api\Request\Education\Workflow\MobileTaskCompleteRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Foundation\MobileContextService;
use App\Service\Education\Workflow\WorkflowTaskService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
class TeacherWorkflowController extends AbstractController
{
    public function __construct(
        private readonly WorkflowTaskService $taskService,
        private readonly MobileContextService $mobileContext,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Get(path: '/mobile/education/workflow/tasks/my', operationId: 'educationMobileWorkflowTasksMy', summary: 'Mobile workflow my tasks', tags: ['Education Mobile Workflow'])]
    #[ResultResponse(instance: new Result())]
    public function my(): Result
    {
        $context = $this->mobileContext->mobile();

        return $this->success($this->taskService->myTasks((int) $context->tenantId, $context->userId, $this->request->all()));
    }

    #[Post(path: '/mobile/education/workflow/tasks/{id}/complete', operationId: 'educationMobileWorkflowTaskComplete', summary: 'Mobile workflow task complete', tags: ['Education Mobile Workflow'])]
    #[ResultResponse(instance: new Result())]
    public function complete(int $id, MobileTaskCompleteRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $data = $request->validated();
        $result = $this->taskService->completeTask($id, $context->userId, (string) $data['result'], (string) $data['content']);
        $this->events->dispatch(new EducationAuditEvent(
            module: 'workflow',
            resource: 'workflow_task',
            action: 'education.workflow.task.completed',
            businessType: 'workflow_task',
            businessId: $id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $result,
            metadata: ['tenant_id' => $context->tenantId, 'campus_id' => $context->currentCampusId],
            summary: 'education.workflow.task.completed',
            actorType: 'teacher'
        ));

        return $this->success($result);
    }

    #[Post(path: '/mobile/education/workflow/tasks/{id}/comments', operationId: 'educationMobileWorkflowTaskComment', summary: 'Mobile workflow task comment', tags: ['Education Mobile Workflow'])]
    #[ResultResponse(instance: new Result())]
    public function comment(int $id, MobileTaskCommentRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $this->taskService->addComment($id, $context->userId, (string) $request->validated()['content']);

        return $this->success(['task_id' => $id, 'commented' => true]);
    }
}

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
use App\Http\Admin\Request\Education\Workflow\OperationAlertPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Workflow\AlertService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
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
final class OperationAlertController extends AbstractController
{
    use WorkflowControllerTrait;

    public function __construct(private readonly AlertService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/workflow/alerts/page', operationId: 'educationWorkflowAlertPage', summary: 'Workflow alert page', tags: ['Education Workflow'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:alert:page')]
    public function page(OperationAlertPageRequest $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->page($context, $request->validated(), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/workflow/alerts/{id}/convert-task', operationId: 'educationWorkflowAlertConvertTask', summary: 'Workflow alert convert task', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:alert:convert')]
    public function convertTask(int $id, RequestInterface $request): Result
    {
        $context = $this->context();
        $body = $request->all();
        $result = $this->service->convertToTask($id, (int) $body['assignee_user_id'], $body['due_at'] ?? null, $context);
        $this->audit($this->events, 'education.workflow.alert.converted', 'operation_alert', $id, $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/workflow/alerts/{id}/ignore', operationId: 'educationWorkflowAlertIgnore', summary: 'Workflow alert ignore', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:alert:convert')]
    public function ignore(int $id): Result
    {
        return $this->success($this->service->setStatus($id, 'ignored', $this->context()));
    }

    #[Post(path: '/admin/education/workflow/alerts/{id}/close', operationId: 'educationWorkflowAlertClose', summary: 'Workflow alert close', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:alert:convert')]
    public function close(int $id): Result
    {
        return $this->success($this->service->setStatus($id, 'closed', $this->context()));
    }
}

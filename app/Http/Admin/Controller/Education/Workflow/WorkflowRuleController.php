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
use App\Http\Admin\Request\Education\Workflow\WorkflowRuleEnableRequest;
use App\Http\Admin\Request\Education\Workflow\WorkflowRuleSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Workflow\WorkflowRuleService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class WorkflowRuleController extends AbstractController
{
    use WorkflowControllerTrait;

    public function __construct(private readonly WorkflowRuleService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/workflow/rules', operationId: 'educationWorkflowRuleSave', summary: 'Workflow rule save', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:rule:save')]
    public function save(WorkflowRuleSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->save($data + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.workflow.rule.saved', 'workflow_rule', $result['rule_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/workflow/rules/{id}/enable', operationId: 'educationWorkflowRuleEnable', summary: 'Workflow rule enable', tags: ['Education Workflow'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:workflow:rule:enable')]
    public function enable(int $id, WorkflowRuleEnableRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->setEnabled($id, (bool) $data['enabled']);
        $this->audit($this->events, 'education.workflow.rule.enabled', 'workflow_rule', $id, $context, $result);

        return $this->success($result);
    }
}

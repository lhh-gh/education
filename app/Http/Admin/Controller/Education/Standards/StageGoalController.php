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

namespace App\Http\Admin\Controller\Education\Standards;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Standards\StageGoalSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Standards\StageGoalService;
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
final class StageGoalController extends AbstractController
{
    use StandardsControllerTrait;

    public function __construct(private readonly StageGoalService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/standards/stage-goals', operationId: 'educationStandardsStageGoalSave', summary: 'Standards stage goal save', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:stage-goal:save')]
    public function save(StageGoalSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $this->campusId($context),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ], $context);
        $this->audit($this->events, 'education.standards.stage_goal.saved', 'stage_goal', $result['stage_goal_id'], $context, $result);

        return $this->success($result);
    }
}

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

namespace App\Http\Admin\Controller\Education\Growth;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Growth\FollowupStrategySaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Growth\FollowupStrategyService;
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
final class FollowupStrategyController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly FollowupStrategyService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/growth/followup-strategies', operationId: 'educationGrowthFollowupStrategySave', summary: 'Growth followup strategy save', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:strategy:save')]
    public function save(FollowupStrategySaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.growth.strategy.saved', 'followup_strategy', $result['strategy_id'], $context, $result);

        return $this->success($result);
    }
}

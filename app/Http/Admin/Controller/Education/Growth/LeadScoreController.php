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
use App\Http\Admin\Request\Education\Growth\LeadScoreRecalculateRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Growth\LeadScoreService;
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
final class LeadScoreController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly LeadScoreService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/growth/leads/{leadId}/score/recalculate', operationId: 'educationGrowthLeadScoreRecalculate', summary: 'Growth lead score recalculate', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:score:recalculate')]
    public function recalculate(int $leadId, LeadScoreRecalculateRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->recalculate($this->tenantId($context), $this->campusId($context), $leadId, $data['score_date'] ?? null);
        $this->audit($this->events, 'education.growth.score.recalculated', 'lead_score', $leadId, $context, $result);

        return $this->success($result);
    }
}

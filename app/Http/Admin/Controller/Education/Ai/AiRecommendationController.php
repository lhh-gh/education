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

namespace App\Http\Admin\Controller\Education\Ai;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiRecommendationService;
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
final class AiRecommendationController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly AiRecommendationService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/recommendation-tasks/page', operationId: 'educationAiRecommendationPage', summary: 'AI recommendation page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:recommendation:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->page($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/recommendation-tasks/{id}/handle', operationId: 'educationAiRecommendationHandle', summary: 'AI recommendation handle', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:recommendation:handle')]
    public function handle(int $id): Result
    {
        $context = $this->context();
        $this->service->markHandled($id);
        $result = ['recommendation_task_id' => $id, 'status' => 'handled'];
        $this->audit($this->events, 'education.ai.recommendation.handled', 'recommendation_task', $id, $context, $result);

        return $this->success($result);
    }
}

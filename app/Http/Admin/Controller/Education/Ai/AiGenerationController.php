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
use App\Http\Admin\Request\Education\Ai\AiGenerationTaskCreateRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiGenerationService;
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
final class AiGenerationController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly AiGenerationService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/generation-tasks/page', operationId: 'educationAiGenerationTaskPage', summary: 'AI generation task page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:generation:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->pageTasks($context, $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/generation-tasks', operationId: 'educationAiGenerationTaskCreate', summary: 'AI generation task create', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:generation:create')]
    public function create(AiGenerationTaskCreateRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->requestAdminGenerationTask($request->validated(), $context);
        $this->audit($this->events, 'education.ai.generation.requested', 'generation_task', $result['task_id'], $context, $result);

        return $this->success($result);
    }

    #[Get(path: '/admin/education/ai/generation-results/{id}/detail', operationId: 'educationAiGenerationResultDetail', summary: 'AI generation result detail', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:generation:page')]
    public function result(int $id): Result
    {
        return $this->success($this->service->resultDetail($id, $this->context()));
    }
}

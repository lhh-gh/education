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
use App\Http\Admin\Request\Education\Ai\AiReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiReviewService;
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
final class AiReviewController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly AiReviewService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/generation-results/page', operationId: 'educationAiReviewPage', summary: 'AI review page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:review:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->pagePending($context, $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/generation-results/{id}/approve', operationId: 'educationAiReviewApprove', summary: 'AI review approve', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:review:approve')]
    public function approve(int $id, AiReviewRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->approve($id, $context, (string) ($data['review_note'] ?? ''), $data['edited_text'] ?? null);
        $this->audit($this->events, 'education.ai.review.approved', 'generation_result', $result['generation_result_id'], $context, $result);

        return $this->success($result);
    }
}

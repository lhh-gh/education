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

namespace App\Http\Admin\Controller\Education\Content;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Content\ContentReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Content\EducationContentReviewRecord;
use App\Service\Education\Content\ContentReviewService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Swagger\Annotation\Get;
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
final class ContentReviewController extends AbstractController
{
    use ContentControllerTrait;

    public function __construct(private readonly ContentReviewService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/content/reviews', operationId: 'educationContentReviewPage', summary: 'Content review page', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:review:page')]
    public function page(RequestInterface $request): Result
    {
        $context = $this->context();
        $query = EducationContentReviewRecord::query()->where('tenant_id', $this->tenantId($context));
        foreach (['business_type', 'business_id', 'status'] as $field) {
            if ($request->input($field) !== null && $request->input($field) !== '') {
                $query->where($field, $request->input($field));
            }
        }
        $total = (int) (clone $query)->count();

        return $this->success([
            'list' => $query->orderByDesc('id')->forPage($this->pageNumber($request), $this->pageSize($request))->get()->toArray(),
            'total' => $total,
        ]);
    }

    #[Post(path: '/admin/education/content/reviews/{id}/review', operationId: 'educationContentReviewHandle', summary: 'Content review handle', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:review:handle')]
    public function review(int $id, ContentReviewRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        try {
            $result = $this->service->reviewExisting($this->tenantId($context), $id, $context->userId, $data['status'], $data['review_note'] ?? null);
        } catch (\Throwable $exception) {
            throw $this->businessFailure($exception);
        }
        $this->audit($this->events, 'education.content.review.handled', 'content_review', $id, $context, $result);

        return $this->success($result);
    }
}

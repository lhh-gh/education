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
use App\Http\Admin\Request\Education\Standards\StandardReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Standards\StandardReviewService;
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
final class StandardReviewController extends AbstractController
{
    use StandardsControllerTrait;

    public function __construct(private readonly StandardReviewService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/standards/reviews/{id}/review', operationId: 'educationStandardsReviewHandle', summary: 'Standards review handle', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:review:handle')]
    public function review(int $id, StandardReviewRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        try {
            $result = $this->service->review($this->tenantId($context), $id, $context->userId, $data['status'], $data['review_note'] ?? null);
        } catch (\RuntimeException $exception) {
            throw $this->businessFailure($exception);
        }
        $this->audit($this->events, 'education.standards.standard.reviewed', 'standard_review', $id, $context, $result);

        return $this->success($result);
    }
}

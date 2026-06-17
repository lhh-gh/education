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

namespace App\Http\Admin\Controller\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Operations\ConsumptionAdjustmentRequest;
use App\Http\Admin\Request\Education\Operations\ConsumptionReviewActionRequest;
use App\Http\Admin\Request\Education\Operations\ConsumptionReviewPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Repository\Education\Operations\ConsumptionReviewRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\ConsumptionReviewService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class ConsumptionReviewController extends AbstractController
{
    public function __construct(
        private readonly ConsumptionReviewService $service,
        private readonly ConsumptionReviewRepository $repository
    ) {}

    #[Get(path: '/admin/education/operations/consumption-reviews/page', operationId: 'educationOperationConsumptionReviewPage', summary: 'Consumption review page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:consumption-review:page')]
    public function page(ConsumptionReviewPageRequest $request): Result
    {
        return $this->success($this->repository->pagePending($request->validated(), $this->context()->tenantId));
    }

    #[Post(path: '/admin/education/operations/consumption-reviews/{id}/approve', operationId: 'educationOperationConsumptionReviewApprove', summary: 'Approve consumption review', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:consumption-review:approve')]
    public function approve(int $id, ConsumptionReviewActionRequest $request): Result
    {
        return $this->success($this->service->approve($id, $this->context(), $request->validated()['review_note'] ?? null));
    }

    #[Post(path: '/admin/education/operations/consumption-reviews/{id}/reject', operationId: 'educationOperationConsumptionReviewReject', summary: 'Reject consumption review', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:consumption-review:approve')]
    public function reject(int $id, ConsumptionReviewActionRequest $request): Result
    {
        return $this->success($this->service->reject($id, $this->context(), $request->validated()['review_note'] ?? null));
    }

    #[Post(path: '/admin/education/operations/lesson-consumptions/{id}/adjust', operationId: 'educationOperationConsumptionAdjust', summary: 'Adjust lesson consumption', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:consumption-adjustment:create')]
    public function adjust(int $id, ConsumptionAdjustmentRequest $request): Result
    {
        $data = $request->validated();
        $data['original_consumption_id'] = $id;

        return $this->success($this->service->createAdjustment($data, $this->context()));
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Operations\LessonBatchChangeRequest;
use App\Http\Admin\Request\Education\Operations\LessonChangeCreateRequest;
use App\Http\Admin\Request\Education\Operations\LessonChangePageRequest;
use App\Http\Admin\Request\Education\Operations\LessonChangeReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Repository\Education\Operations\LessonChangeRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\LessonChangeService;
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
final class LessonChangeController extends AbstractController
{
    public function __construct(
        private readonly LessonChangeService $service,
        private readonly LessonChangeRepository $repository
    ) {}

    #[Get(path: '/admin/education/operations/lesson-change-requests/page', operationId: 'educationOperationLessonChangePage', summary: 'Lesson change requests', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:page')]
    public function page(LessonChangePageRequest $request): Result
    {
        return $this->success($this->repository->pageByCampusScope($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/operations/lesson-change-requests', operationId: 'educationOperationLessonChangeCreate', summary: 'Create lesson change request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:create')]
    public function create(LessonChangeCreateRequest $request): Result
    {
        return $this->success($this->service->createRequest($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/operations/lesson-change-requests/{id}/approve', operationId: 'educationOperationLessonChangeApprove', summary: 'Approve lesson change request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:approve')]
    public function approve(int $id, LessonChangeReviewRequest $request): Result
    {
        return $this->success($this->service->approve($id, $request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/operations/lesson-change-requests/{id}/reject', operationId: 'educationOperationLessonChangeReject', summary: 'Reject lesson change request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:reject')]
    public function reject(int $id, LessonChangeReviewRequest $request): Result
    {
        return $this->success($this->service->reject($id, $this->context()));
    }

    #[Post(path: '/admin/education/operations/lesson-change-requests/{id}/apply', operationId: 'educationOperationLessonChangeApply', summary: 'Apply lesson change request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:apply')]
    public function apply(int $id): Result
    {
        return $this->success($this->service->apply($id, $this->context()));
    }

    #[Post(path: '/admin/education/operations/lessons/batch-change', operationId: 'educationOperationLessonBatchChange', summary: 'Batch change lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:lesson-change:batch')]
    public function batchChange(LessonBatchChangeRequest $request): Result
    {
        $data = $request->validated();

        return $this->success($this->service->batchChange($data['lesson_ids'], (string) $data['change_type'], (string) $data['reason'], $this->context()));
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

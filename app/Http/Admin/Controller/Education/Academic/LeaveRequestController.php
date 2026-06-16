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

namespace App\Http\Admin\Controller\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Academic\LeaveRequestCancelRequest;
use App\Http\Admin\Request\Education\Academic\LeaveRequestCreateRequest;
use App\Http\Admin\Request\Education\Academic\LeaveRequestPageRequest;
use App\Http\Admin\Request\Education\Academic\LeaveRequestReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\LeaveRequestSchema;
use App\Service\Education\Academic\LeaveRequestService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class LeaveRequestController extends AbstractController
{
    public function __construct(
        private readonly LeaveRequestService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/leave-requests/page', operationId: 'educationAcademicLeaveRequestPage', summary: 'Leave request page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: LeaveRequestSchema::class)]
    #[Permission(code: 'education:academic:leave-request:page')]
    public function page(LeaveRequestPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/leave-requests/{id}', operationId: 'educationAcademicLeaveRequestDetail', summary: 'Leave request detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:leave-request:detail')]
    public function detail(int $id): Result
    {
        return $this->success((new LeaveRequestSchema($this->service->detail($id, $this->context())))->jsonSerialize());
    }

    #[Post(path: '/admin/education/academic/leave-requests', operationId: 'educationAcademicLeaveRequestCreate', summary: 'Create leave request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LeaveRequestCreateRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:leave-request:create')]
    public function create(LeaveRequestCreateRequest $request): Result
    {
        return $this->success($this->service->create($request->validated(), $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/leave-requests/{id}/approve', operationId: 'educationAcademicLeaveRequestApprove', summary: 'Approve leave request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LeaveRequestReviewRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:leave-request:approve')]
    public function approve(int $id, LeaveRequestReviewRequest $request): Result
    {
        return $this->success($this->service->approve($id, $request->validated()['review_remark'], $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/leave-requests/{id}/reject', operationId: 'educationAcademicLeaveRequestReject', summary: 'Reject leave request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LeaveRequestReviewRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:leave-request:reject')]
    public function reject(int $id, LeaveRequestReviewRequest $request): Result
    {
        return $this->success($this->service->reject($id, $request->validated()['review_remark'], $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/leave-requests/{id}/cancel', operationId: 'educationAcademicLeaveRequestCancel', summary: 'Cancel leave request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LeaveRequestCancelRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:leave-request:cancel')]
    public function cancel(int $id, LeaveRequestCancelRequest $request): Result
    {
        return $this->success($this->service->cancel($id, $request->validated()['cancel_reason'], $this->context(), $this->currentUser->id())->toArray());
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

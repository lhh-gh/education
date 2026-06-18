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

namespace App\Http\Api\Controller\Education\Academic;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Academic\TeacherLeaveDetailRequest;
use App\Http\Api\Request\Education\Academic\TeacherLeavePageRequest;
use App\Http\Api\Request\Education\Academic\TeacherLeaveReviewRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\TeacherMobileLeaveService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherLeaveController extends AbstractController
{
    public function __construct(
        private readonly TeacherMobileLeaveService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/academic/teacher/leave-requests/page', operationId: 'educationMobileTeacherLeavePage', summary: 'Teacher leave page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[PageResponse(instance: new Result())]
    public function page(TeacherLeavePageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/academic/teacher/leave-requests/{id}', operationId: 'educationMobileTeacherLeaveDetail', summary: 'Teacher leave detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function detail(int $id, TeacherLeaveDetailRequest $request): Result
    {
        return $this->success($this->service->detail($id, $request->validated(), $this->mobileContext->mobile()));
    }

    #[Put(path: '/mobile/education/academic/teacher/leave-requests/{id}/approve', operationId: 'educationMobileTeacherLeaveApprove', summary: 'Teacher leave approve', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[Middleware(middleware: OperationMiddleware::class, priority: 98)]
    #[RequestBody(content: new JsonContent(ref: TeacherLeaveReviewRequest::class))]
    #[ResultResponse(instance: new Result())]
    public function approve(int $id, TeacherLeaveReviewRequest $request): Result
    {
        return $this->success($this->service->approve($id, $request->validated(), $this->mobileContext->mobile()));
    }

    #[Put(path: '/mobile/education/academic/teacher/leave-requests/{id}/reject', operationId: 'educationMobileTeacherLeaveReject', summary: 'Teacher leave reject', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[Middleware(middleware: OperationMiddleware::class, priority: 98)]
    #[RequestBody(content: new JsonContent(ref: TeacherLeaveReviewRequest::class))]
    #[ResultResponse(instance: new Result())]
    public function reject(int $id, TeacherLeaveReviewRequest $request): Result
    {
        return $this->success($this->service->reject($id, $request->validated(), $this->mobileContext->mobile()));
    }
}

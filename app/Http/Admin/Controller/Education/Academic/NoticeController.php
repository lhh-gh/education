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
use App\Http\Admin\Request\Education\Academic\NoticePageRequest;
use App\Http\Admin\Request\Education\Academic\NoticePublishRequest;
use App\Http\Admin\Request\Education\Academic\NoticeReceiptPageRequest;
use App\Http\Admin\Request\Education\Academic\NoticeSaveRequest;
use App\Http\Admin\Request\Education\Academic\NoticeWithdrawRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\NoticeSchema;
use App\Service\Education\Academic\NoticeService;
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
final class NoticeController extends AbstractController
{
    public function __construct(
        private readonly NoticeService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/notices/page', operationId: 'educationAcademicNoticePage', summary: 'Notice page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: NoticeSchema::class)]
    #[Permission(code: 'education:academic:notice:page')]
    public function page(NoticePageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/notices/{id}', operationId: 'educationAcademicNoticeDetail', summary: 'Notice detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:detail')]
    public function detail(int $id): Result
    {
        return $this->success($this->service->detail($id, $this->context()));
    }

    #[Post(path: '/admin/education/academic/notices', operationId: 'educationAcademicNoticeCreate', summary: 'Create notice', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: NoticeSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:create')]
    public function create(NoticeSaveRequest $request): Result
    {
        return $this->success((new NoticeSchema($this->service->create($request->validated(), $this->context(), $this->currentUser->id())))->jsonSerialize());
    }

    #[Put(path: '/admin/education/academic/notices/{id}', operationId: 'educationAcademicNoticeUpdate', summary: 'Update notice', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: NoticeSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:update')]
    public function update(int $id, NoticeSaveRequest $request): Result
    {
        return $this->success((new NoticeSchema($this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id())))->jsonSerialize());
    }

    #[Put(path: '/admin/education/academic/notices/{id}/publish', operationId: 'educationAcademicNoticePublish', summary: 'Publish notice', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: NoticePublishRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:publish')]
    public function publish(int $id, NoticePublishRequest $request): Result
    {
        return $this->success($this->service->publish($id, $request->validated(), $this->context(), $this->currentUser->id()));
    }

    #[Put(path: '/admin/education/academic/notices/{id}/withdraw', operationId: 'educationAcademicNoticeWithdraw', summary: 'Withdraw notice', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: NoticeWithdrawRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:withdraw')]
    public function withdraw(int $id, NoticeWithdrawRequest $request): Result
    {
        return $this->success((new NoticeSchema($this->service->withdraw($id, $request->validated(), $this->context(), $this->currentUser->id())))->jsonSerialize());
    }

    #[Get(path: '/admin/education/academic/notices/{id}/receipts/page', operationId: 'educationAcademicNoticeReceiptPage', summary: 'Notice receipt page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:academic:notice:receipt')]
    public function receipts(int $id, NoticeReceiptPageRequest $request): Result
    {
        return $this->success($this->service->receipts($id, $request->validated(), $this->context()));
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

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
use App\Http\Admin\Request\Education\Academic\AccountAdjustmentCreateRequest;
use App\Http\Admin\Request\Education\Academic\AccountAdjustmentPageRequest;
use App\Http\Admin\Request\Education\Academic\AccountAdjustmentRollbackRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\AccountAdjustmentSchema;
use App\Service\Education\Academic\AccountAdjustmentService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class AccountAdjustmentController extends AbstractController
{
    public function __construct(
        private readonly AccountAdjustmentService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/account-adjustments/page', operationId: 'educationAcademicAccountAdjustmentPage', summary: 'Account adjustment page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: AccountAdjustmentSchema::class)]
    #[Permission(code: 'education:academic:account-adjustment:page')]
    public function page(AccountAdjustmentPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/account-adjustments/{id}', operationId: 'educationAcademicAccountAdjustmentDetail', summary: 'Account adjustment detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:account-adjustment:detail')]
    public function detail(int $id): Result
    {
        return $this->success($this->service->detail($id, $this->context())->toArray());
    }

    #[Post(path: '/admin/education/academic/account-adjustments', operationId: 'educationAcademicAccountAdjustmentCreate', summary: 'Create account adjustment', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: AccountAdjustmentCreateRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:account-adjustment:create')]
    public function create(AccountAdjustmentCreateRequest $request): Result
    {
        return $this->success($this->service->createSupplementDeduction($request->validated(), $this->context(), $this->currentUser->id()));
    }

    #[Post(path: '/admin/education/academic/account-adjustments/{id}/rollback', operationId: 'educationAcademicAccountAdjustmentRollback', summary: 'Rollback account adjustment', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: AccountAdjustmentRollbackRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:account-adjustment:rollback')]
    public function rollback(int $id, AccountAdjustmentRollbackRequest $request): Result
    {
        return $this->success($this->service->rollback($id, $request->validated()['reason'], $this->context(), $this->currentUser->id()));
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

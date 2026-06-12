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

namespace App\Http\Admin\Controller\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\FeatureFlagPageRequest;
use App\Http\Admin\Request\Education\Foundation\FeatureFlagSaveRequest;
use App\Http\Admin\Request\Education\Foundation\FeatureFlagStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Foundation\FeatureFlagSchema;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\FeatureFlagService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
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
final class FeatureFlagController extends AbstractController
{
    public function __construct(
        private readonly FeatureFlagService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/education/foundation/feature-flags/page',
        operationId: 'educationFeatureFlagPage',
        summary: 'Feature flag page',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[PageResponse(instance: FeatureFlagSchema::class)]
    #[Permission(code: 'education:foundation:feature-flag:page')]
    public function page(FeatureFlagPageRequest $request): Result
    {
        return $this->success(
            $this->service->page(
                $request->validated(),
                $this->getCurrentPage(),
                $this->getPageSize(),
                $this->context()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/feature-flags',
        operationId: 'educationFeatureFlagCreate',
        summary: 'Create feature flag',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: FeatureFlagSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:feature-flag:create')]
    public function create(FeatureFlagSaveRequest $request): Result
    {
        $flag = $this->service->createFlag($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $flag->id, 'owner_key' => $flag->owner_key]);
    }

    #[Put(
        path: '/admin/education/foundation/feature-flags/{id}',
        operationId: 'educationFeatureFlagUpdate',
        summary: 'Update feature flag',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: FeatureFlagSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:feature-flag:update')]
    public function update(int $id, FeatureFlagSaveRequest $request): Result
    {
        $flag = $this->service->updateFlag($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $flag->id]);
    }

    #[Put(
        path: '/admin/education/foundation/feature-flags/{id}/status',
        operationId: 'educationFeatureFlagStatus',
        summary: 'Update feature flag status',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: FeatureFlagStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:feature-flag:status')]
    public function status(int $id, FeatureFlagStatusRequest $request): Result
    {
        $flag = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $flag->id, 'status' => $flag->status]);
    }

    #[Delete(
        path: '/admin/education/foundation/feature-flags/{id}',
        operationId: 'educationFeatureFlagDelete',
        summary: 'Delete feature flag',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:feature-flag:delete')]
    public function delete(int $id): Result
    {
        $this->service->deleteFlag($id, $this->context());

        return $this->success();
    }

    #[Get(
        path: '/admin/education/foundation/feature-flags/{featureCode}/resolved',
        operationId: 'educationFeatureFlagResolved',
        summary: 'Resolved feature flag',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:feature-flag:lookup')]
    public function resolved(string $featureCode): Result
    {
        return $this->success($this->service->resolved($featureCode, $this->context()->tenantId));
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

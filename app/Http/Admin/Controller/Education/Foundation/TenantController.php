<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Education\Foundation;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\TenantPageRequest;
use App\Http\Admin\Request\Education\Foundation\TenantSaveRequest;
use App\Http\Admin\Request\Education\Foundation\TenantStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\Education\Foundation\TenantSchema;
use App\Service\Education\Foundation\TenantService;
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
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class TenantController extends AbstractController
{
    public function __construct(
        private readonly TenantService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/education/foundation/tenants/page',
        operationId: 'educationTenantPage',
        summary: '教育租户分页',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[PageResponse(instance: TenantSchema::class)]
    #[Permission(code: 'education:foundation:tenant:page')]
    public function page(TenantPageRequest $request): Result
    {
        return $this->success(
            $this->service->page(
                $request->validated(),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/tenants',
        operationId: 'educationTenantCreate',
        summary: '创建教育租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:tenant:create')]
    public function create(TenantSaveRequest $request): Result
    {
        $tenant = $this->service->createTenant(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
        ]));

        return $this->success(['id' => $tenant->id]);
    }

    #[Put(
        path: '/admin/education/foundation/tenants/{id}',
        operationId: 'educationTenantUpdate',
        summary: '更新教育租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:tenant:update')]
    public function update(int $id, TenantSaveRequest $request): Result
    {
        $tenant = $this->service->updateTenant($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));

        return $this->success(['id' => $tenant->id]);
    }

    #[Put(
        path: '/admin/education/foundation/tenants/{id}/status',
        operationId: 'educationTenantStatus',
        summary: '更新教育租户状态',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:tenant:status')]
    public function status(int $id, TenantStatusRequest $request): Result
    {
        $tenant = $this->service->changeStatus($id, $request->validated()['status'], $this->currentUser->id());

        return $this->success(['id' => $tenant->id, 'status' => $tenant->status->value ?? $tenant->status]);
    }

    #[Delete(
        path: '/admin/education/foundation/tenants/{id}',
        operationId: 'educationTenantDelete',
        summary: '删除教育租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:tenant:delete')]
    public function delete(int $id): Result
    {
        $this->service->deleteTenant($id);

        return $this->success();
    }
}

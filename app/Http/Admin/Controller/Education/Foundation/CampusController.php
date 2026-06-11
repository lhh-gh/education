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

use App\Contract\Education\Foundation\TenantContextInterface;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\CampusPageRequest;
use App\Http\Admin\Request\Education\Foundation\CampusSaveRequest;
use App\Http\Admin\Request\Education\Foundation\CampusStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\Education\Foundation\CampusSchema;
use App\Service\Education\Foundation\CampusService;
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
final class CampusController extends AbstractController
{
    public function __construct(
        private readonly CampusService $service,
        private readonly TenantContextInterface $tenantContext,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/education/foundation/campuses/page',
        operationId: 'educationCampusPage',
        summary: '教育校区分页',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[PageResponse(instance: CampusSchema::class)]
    #[Permission(code: 'education:foundation:campus:page')]
    public function page(CampusPageRequest $request): Result
    {
        return $this->success(
            $this->service->page(
                array_merge($request->validated(), ['tenant_id' => $this->tenantContext->id()]),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/campuses',
        operationId: 'educationCampusCreate',
        summary: '创建教育校区',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: CampusSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus:create')]
    public function create(CampusSaveRequest $request): Result
    {
        $campus = $this->service->createCampus($this->tenantContext->id(), array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
        ]));

        return $this->success(['id' => $campus->id, 'tenant_id' => $campus->tenant_id]);
    }

    #[Put(
        path: '/admin/education/foundation/campuses/{id}',
        operationId: 'educationCampusUpdate',
        summary: '更新教育校区',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: CampusSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus:update')]
    public function update(int $id, CampusSaveRequest $request): Result
    {
        $campus = $this->service->updateCampus($this->tenantContext->id(), $id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));

        return $this->success(['id' => $campus->id, 'tenant_id' => $campus->tenant_id]);
    }

    #[Put(
        path: '/admin/education/foundation/campuses/{id}/status',
        operationId: 'educationCampusStatus',
        summary: '更新教育校区状态',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[RequestBody(content: new JsonContent(ref: CampusStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus:status')]
    public function status(int $id, CampusStatusRequest $request): Result
    {
        $campus = $this->service->changeStatus(
            $this->tenantContext->id(),
            $id,
            $request->validated()['status'],
            $this->currentUser->id()
        );

        return $this->success([
            'id' => $campus->id,
            'tenant_id' => $campus->tenant_id,
            'status' => $campus->status->value ?? $campus->status,
        ]);
    }

    #[Delete(
        path: '/admin/education/foundation/campuses/{id}',
        operationId: 'educationCampusDelete',
        summary: '删除教育校区',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['教育基础'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus:delete')]
    public function delete(int $id): Result
    {
        $this->service->deleteCampus($this->tenantContext->id(), $id);

        return $this->success();
    }
}

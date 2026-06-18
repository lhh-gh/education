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

namespace App\Http\Admin\Controller\Education\Group;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Group\DataPermissionSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\DataPermissionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class DataPermissionController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly DataPermissionService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/group/data-permissions/page', operationId: 'educationGroupDataPermissionPage', summary: 'Group data permission page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:data-permission:page')]
    public function page(): Result
    {
        return $this->success($this->service->page($this->getRequestData(), $this->context()));
    }

    #[Get(path: '/admin/education/group/data-permissions/preview', operationId: 'educationGroupDataPermissionPreview', summary: 'Group data permission preview', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:data-permission:preview')]
    public function preview(): Result
    {
        return $this->success($this->service->preview($this->context()));
    }

    #[Post(path: '/admin/education/group/data-permissions', operationId: 'educationGroupDataPermissionSave', summary: 'Group data permission save', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:data-permission:save')]
    public function save(DataPermissionSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->saveUserPermission($request->validated(), $context);
        $this->audit($this->events, 'education.group.data_permission.saved', 'data_permission', $result['user_id'], $context, $result);

        return $this->success($result);
    }
}

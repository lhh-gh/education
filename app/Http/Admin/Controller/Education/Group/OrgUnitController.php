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
use App\Http\Admin\Request\Education\Group\CampusOrgRelationSaveRequest;
use App\Http\Admin\Request\Education\Group\OrgUnitSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\OrgUnitService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class OrgUnitController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly OrgUnitService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/group/org-units/tree', operationId: 'educationGroupOrgTree', summary: 'Group org tree', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:org:tree')]
    public function tree(): Result
    {
        return $this->success($this->service->tree($this->context(), $this->getRequestData()));
    }

    #[Post(path: '/admin/education/group/org-units', operationId: 'educationGroupOrgSave', summary: 'Group org save', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:org:create')]
    public function save(OrgUnitSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated(), $context);
        $this->audit($this->events, 'education.group.org.saved', 'org_unit', $result['id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/group/campus-org-relations', operationId: 'educationGroupCampusOrgRelationSave', summary: 'Group campus org relation save', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:org:create')]
    public function bindCampus(CampusOrgRelationSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->bindCampus($request->validated(), $context);
        $this->audit($this->events, 'education.group.campus_relation.saved', 'campus_org_relation', $result['org_unit_id'], $context, $result);

        return $this->success($result);
    }
}

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
use App\Http\Admin\Request\Education\Group\FranchiseRecordSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\FranchiseService;
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
final class FranchiseController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly FranchiseService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/group/franchises/page', operationId: 'educationGroupFranchisePage', summary: 'Group franchise page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:franchise:page')]
    public function page(): Result
    {
        return $this->success($this->service->page($this->getRequestData(), $this->context()));
    }

    #[Post(path: '/admin/education/group/franchises', operationId: 'educationGroupFranchiseSave', summary: 'Group franchise save', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:franchise:save')]
    public function save(FranchiseRecordSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated(), $context);
        $this->audit($this->events, 'education.group.franchise.saved', 'franchise', $result['id'], $context, $result);

        return $this->success($result);
    }
}

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

namespace App\Http\Admin\Controller\Education\Content;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Content\MaterialVersionSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Content\MaterialVersionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class MaterialVersionController extends AbstractController
{
    use ContentControllerTrait;

    public function __construct(private readonly MaterialVersionService $service) {}

    #[Get(path: '/admin/education/content/material-versions', operationId: 'educationContentMaterialVersionPage', summary: 'Content material version page', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:version:page')]
    public function page(RequestInterface $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->page(
            $this->tenantId($context),
            (int) $request->input('material_id', 0),
            $this->pageNumber($request),
            $this->pageSize($request)
        ));
    }

    #[Get(path: '/admin/education/content/material-versions/{id}', operationId: 'educationContentMaterialVersionDetail', summary: 'Content version detail', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:version:page')]
    public function detail(int $id): Result
    {
        $context = $this->context();

        return $this->success($this->service->detail($this->tenantId($context), $id));
    }

    #[Post(path: '/admin/education/content/materials/{id}/versions', operationId: 'educationContentMaterialVersionCreate', summary: 'Content version create', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:version:create')]
    public function save(int $id, MaterialVersionSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();

        return $this->success($this->service->save($data + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'material_id' => $id,
        ]));
    }
}

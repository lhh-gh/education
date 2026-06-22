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
use App\Http\Admin\Request\Education\Content\ShowcaseSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Content\ShowcaseService;
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
final class ShowcaseController extends AbstractController
{
    use ContentControllerTrait;

    public function __construct(private readonly ShowcaseService $service) {}

    #[Get(path: '/admin/education/content/showcases', operationId: 'educationContentShowcasePage', summary: 'Content showcase page', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:showcase:page')]
    public function page(RequestInterface $request): Result
    {
        $context = $this->context();

        return $this->success($this->service->page(
            $request->all(),
            $context,
            $this->pageNumber($request),
            $this->pageSize($request)
        ));
    }

    #[Post(path: '/admin/education/content/showcases', operationId: 'educationContentShowcaseSave', summary: 'Content showcase save', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:showcase:save')]
    public function save(ShowcaseSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        if (isset($data['showcase_id'])) {
            $data['id'] = $data['showcase_id'];
        }

        return $this->success($this->service->save($data + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
        ]));
    }

    #[Post(path: '/admin/education/content/showcases/{id}/publish', operationId: 'educationContentShowcasePublish', summary: 'Content showcase publish', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:showcase:publish')]
    public function publish(int $id): Result
    {
        $context = $this->context();

        return $this->success($this->service->publish($context, $id));
    }

    #[Post(path: '/admin/education/content/showcases/{id}/withdraw', operationId: 'educationContentShowcaseWithdraw', summary: 'Content showcase withdraw', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:showcase:withdraw')]
    public function withdraw(int $id): Result
    {
        $context = $this->context();

        return $this->success($this->service->withdraw($context, $id));
    }
}

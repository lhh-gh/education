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

namespace App\Http\Admin\Controller\Education\Family;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Family\PerformanceTagSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\CommentTemplateService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class PerformanceTagController extends AbstractController
{
    use FamilyControllerTrait;

    public function __construct(private readonly CommentTemplateService $service) {}

    #[Get(path: '/admin/education/family/performance-tags/page', operationId: 'educationFamilyPerformanceTagPage', summary: 'Family performance tag page', tags: ['Education Family'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:family:performance-tag:page')]
    public function page(): Result
    {
        return $this->success($this->service->pageTags($this->getRequestData(), $this->context()));
    }

    #[Post(path: '/admin/education/family/performance-tags', operationId: 'educationFamilyPerformanceTagSave', summary: 'Family performance tag save', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:performance-tag:create')]
    public function save(PerformanceTagSaveRequest $request): Result
    {
        return $this->success($this->service->saveTag($request->validated(), $this->context()));
    }
}

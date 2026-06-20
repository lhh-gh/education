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
use App\Http\Admin\Request\Education\Content\MaterialRelationSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Content\EducationLearningMaterialRelation;
use App\Service\Education\Content\MaterialRelationService;
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
final class MaterialRelationController extends AbstractController
{
    use ContentControllerTrait;

    public function __construct(private readonly MaterialRelationService $service) {}

    #[Get(path: '/admin/education/content/material-relations', operationId: 'educationContentMaterialRelationPage', summary: 'Content material relation page', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:relation:page')]
    public function page(RequestInterface $request): Result
    {
        $context = $this->context();
        $query = EducationLearningMaterialRelation::query()->where('tenant_id', $this->tenantId($context));
        foreach (['material_id', 'target_id'] as $field) {
            if ($request->input($field) !== null && $request->input($field) !== '') {
                $query->where($field, (int) $request->input($field));
            }
        }
        if ($request->input('target_type')) {
            $query->where('target_type', (string) $request->input('target_type'));
        }

        $total = (int) (clone $query)->count();

        return $this->success([
            'list' => $query->orderByDesc('id')->forPage($this->pageNumber($request), $this->pageSize($request))->get()->toArray(),
            'total' => $total,
        ]);
    }

    #[Post(path: '/admin/education/content/materials/{id}/relations', operationId: 'educationContentMaterialRelationSave', summary: 'Content material relation save', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:relation:save')]
    public function save(int $id, MaterialRelationSaveRequest $request): Result
    {
        $context = $this->context();
        $this->service->saveMaterialRelations($this->tenantId($context), $context->currentCampusId, $id, $request->validated()['relations'] ?? []);

        return $this->success(['material_id' => $id, 'status' => 'saved']);
    }
}

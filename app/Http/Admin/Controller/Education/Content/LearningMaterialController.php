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
use App\Http\Admin\Request\Education\Content\LearningMaterialSaveRequest;
use App\Http\Admin\Request\Education\Content\MaterialPublishRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Content\LearningMaterialService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
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
final class LearningMaterialController extends AbstractController
{
    use ContentControllerTrait;

    public function __construct(private readonly LearningMaterialService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/content/materials', operationId: 'educationContentMaterialPage', summary: 'Content material page', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:material:page')]
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

    #[Post(path: '/admin/education/content/materials', operationId: 'educationContentMaterialSave', summary: 'Content material save', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:material:save')]
    public function save(LearningMaterialSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        if (isset($data['material_id'])) {
            $data['id'] = $data['material_id'];
        }
        $result = $this->service->save($data + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.content.material.saved', 'learning_material', $result['material_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/content/materials/{id}/publish', operationId: 'educationContentMaterialPublish', summary: 'Content material publish', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:material:publish')]
    public function publish(int $id, MaterialPublishRequest $request): Result
    {
        $context = $this->context();
        try {
            $result = $this->service->publish($this->tenantId($context), $id, $context->userId, true);
        } catch (\RuntimeException $exception) {
            throw $this->businessFailure($exception);
        }
        $this->audit($this->events, 'education.content.material.published', 'learning_material', $id, $context, $result + $request->validated());

        return $this->success($result);
    }

    #[Post(path: '/admin/education/content/materials/{id}/withdraw', operationId: 'educationContentMaterialWithdraw', summary: 'Content material withdraw', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:material:publish')]
    public function withdraw(int $id): Result
    {
        $context = $this->context();
        $result = $this->service->withdraw($this->tenantId($context), $id, $context->userId);
        $this->audit($this->events, 'education.content.material.withdrawn', 'learning_material', $id, $context, $result);

        return $this->success($result);
    }
}

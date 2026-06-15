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

namespace App\Http\Admin\Controller\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Academic\GuardianPageRequest;
use App\Http\Admin\Request\Education\Academic\GuardianSaveRequest;
use App\Http\Admin\Request\Education\Academic\GuardianStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\GuardianSchema;
use App\Service\Education\Academic\GuardianService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
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
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class GuardianController extends AbstractController
{
    public function __construct(
        private readonly GuardianService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/guardians/page', operationId: 'educationAcademicGuardianPage', summary: 'Guardian page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: GuardianSchema::class)]
    #[Permission(code: 'education:academic:guardian:page')]
    public function page(GuardianPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/guardians', operationId: 'educationAcademicGuardianCreate', summary: 'Create guardian', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: GuardianSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:guardian:create')]
    public function create(GuardianSaveRequest $request): Result
    {
        $guardian = $this->service->create($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($guardian->toArray());
    }

    #[Put(path: '/admin/education/academic/guardians/{id}', operationId: 'educationAcademicGuardianUpdate', summary: 'Update guardian', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: GuardianSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:guardian:update')]
    public function update(int $id, GuardianSaveRequest $request): Result
    {
        $guardian = $this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($guardian->toArray());
    }

    #[Put(path: '/admin/education/academic/guardians/{id}/status', operationId: 'educationAcademicGuardianStatus', summary: 'Guardian status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: GuardianStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:guardian:status')]
    public function status(int $id, GuardianStatusRequest $request): Result
    {
        $guardian = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $guardian->id, 'status' => $guardian->status]);
    }

    #[Delete(path: '/admin/education/academic/guardians/{id}', operationId: 'educationAcademicGuardianDelete', summary: 'Delete guardian', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:guardian:delete')]
    public function delete(int $id): Result
    {
        $this->service->delete($id, $this->context(), $this->currentUser->id());

        return $this->success();
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}

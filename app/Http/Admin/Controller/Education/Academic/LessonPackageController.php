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
use App\Http\Admin\Request\Education\Academic\LessonPackagePageRequest;
use App\Http\Admin\Request\Education\Academic\LessonPackageSaveRequest;
use App\Http\Admin\Request\Education\Academic\LessonPackageStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\LessonPackageSchema;
use App\Service\Education\Academic\LessonPackageService;
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
final class LessonPackageController extends AbstractController
{
    public function __construct(
        private readonly LessonPackageService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/lesson-packages/page', operationId: 'educationAcademicLessonPackagePage', summary: 'Lesson package page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: LessonPackageSchema::class)]
    #[Permission(code: 'education:academic:lesson-package:page')]
    public function page(LessonPackagePageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/lesson-packages', operationId: 'educationAcademicLessonPackageCreate', summary: 'Create lesson package', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LessonPackageSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-package:create')]
    public function create(LessonPackageSaveRequest $request): Result
    {
        $package = $this->service->create($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($package->toArray());
    }

    #[Put(path: '/admin/education/academic/lesson-packages/{id}', operationId: 'educationAcademicLessonPackageUpdate', summary: 'Update lesson package', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LessonPackageSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-package:update')]
    public function update(int $id, LessonPackageSaveRequest $request): Result
    {
        $package = $this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($package->toArray());
    }

    #[Put(path: '/admin/education/academic/lesson-packages/{id}/status', operationId: 'educationAcademicLessonPackageStatus', summary: 'Lesson package status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LessonPackageStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-package:status')]
    public function status(int $id, LessonPackageStatusRequest $request): Result
    {
        $package = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $package->id, 'status' => $package->status]);
    }

    #[Delete(path: '/admin/education/academic/lesson-packages/{id}', operationId: 'educationAcademicLessonPackageDelete', summary: 'Delete lesson package', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-package:delete')]
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

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
use App\Http\Admin\Request\Education\Academic\TeacherPageRequest;
use App\Http\Admin\Request\Education\Academic\TeacherSaveRequest;
use App\Http\Admin\Request\Education\Academic\TeacherStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\TeacherSchema;
use App\Service\Education\Academic\TeacherService;
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
final class TeacherController extends AbstractController
{
    public function __construct(
        private readonly TeacherService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/teachers/page', operationId: 'educationAcademicTeacherPage', summary: 'Teacher page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: TeacherSchema::class)]
    #[Permission(code: 'education:academic:teacher:page')]
    public function page(TeacherPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/teachers', operationId: 'educationAcademicTeacherCreate', summary: 'Create teacher', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: TeacherSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:teacher:create')]
    public function create(TeacherSaveRequest $request): Result
    {
        $teacher = $this->service->create($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($teacher->toArray());
    }

    #[Put(path: '/admin/education/academic/teachers/{id}', operationId: 'educationAcademicTeacherUpdate', summary: 'Update teacher', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: TeacherSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:teacher:update')]
    public function update(int $id, TeacherSaveRequest $request): Result
    {
        $teacher = $this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($teacher->toArray());
    }

    #[Put(path: '/admin/education/academic/teachers/{id}/status', operationId: 'educationAcademicTeacherStatus', summary: 'Teacher status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: TeacherStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:teacher:status')]
    public function status(int $id, TeacherStatusRequest $request): Result
    {
        $teacher = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $teacher->id, 'status' => $teacher->status]);
    }

    #[Delete(path: '/admin/education/academic/teachers/{id}', operationId: 'educationAcademicTeacherDelete', summary: 'Delete teacher', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:teacher:delete')]
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

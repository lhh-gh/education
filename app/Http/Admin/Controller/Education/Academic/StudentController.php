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
use App\Http\Admin\Request\Education\Academic\StudentGuardianSaveRequest;
use App\Http\Admin\Request\Education\Academic\StudentPageRequest;
use App\Http\Admin\Request\Education\Academic\StudentSaveRequest;
use App\Http\Admin\Request\Education\Academic\StudentStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\StudentSchema;
use App\Service\Education\Academic\StudentService;
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
final class StudentController extends AbstractController
{
    public function __construct(
        private readonly StudentService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/students/page', operationId: 'educationAcademicStudentPage', summary: 'Student page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: StudentSchema::class)]
    #[Permission(code: 'education:academic:student:page')]
    public function page(StudentPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/students', operationId: 'educationAcademicStudentCreate', summary: 'Create student', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: StudentSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student:create')]
    public function create(StudentSaveRequest $request): Result
    {
        $student = $this->service->create($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($student->toArray());
    }

    #[Put(path: '/admin/education/academic/students/{id}', operationId: 'educationAcademicStudentUpdate', summary: 'Update student', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: StudentSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student:update')]
    public function update(int $id, StudentSaveRequest $request): Result
    {
        $student = $this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($student->toArray());
    }

    #[Put(path: '/admin/education/academic/students/{id}/status', operationId: 'educationAcademicStudentStatus', summary: 'Student status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: StudentStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student:status')]
    public function status(int $id, StudentStatusRequest $request): Result
    {
        $student = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $student->id, 'status' => $student->status]);
    }

    #[Delete(path: '/admin/education/academic/students/{id}', operationId: 'educationAcademicStudentDelete', summary: 'Delete student', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student:delete')]
    public function delete(int $id): Result
    {
        $this->service->delete($id, $this->context(), $this->currentUser->id());

        return $this->success();
    }

    #[Get(path: '/admin/education/academic/students/{id}/guardians', operationId: 'educationAcademicStudentGuardians', summary: 'Student guardians', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student-guardian:page')]
    public function guardians(int $id): Result
    {
        return $this->success(['list' => $this->service->guardians($id, $this->context())]);
    }

    #[Put(path: '/admin/education/academic/students/{id}/guardians', operationId: 'educationAcademicStudentSaveGuardians', summary: 'Save student guardians', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: StudentGuardianSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:student-guardian:save')]
    public function saveGuardians(int $id, StudentGuardianSaveRequest $request): Result
    {
        return $this->success(['list' => $this->service->saveGuardians($id, $request->validated()['relations'], $this->context(), $this->currentUser->id())]);
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

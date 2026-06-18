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
use App\Http\Admin\Request\Education\Academic\ClassPageRequest;
use App\Http\Admin\Request\Education\Academic\ClassSaveRequest;
use App\Http\Admin\Request\Education\Academic\ClassStatusRequest;
use App\Http\Admin\Request\Education\Academic\ClassStudentSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\ClassSchema;
use App\Service\Education\Academic\ClassService;
use App\Service\Education\Academic\ClassStudentService;
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
final class ClassController extends AbstractController
{
    public function __construct(
        private readonly ClassService $service,
        private readonly ClassStudentService $studentService,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/classes/page', operationId: 'educationAcademicClassPage', summary: 'Class page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: ClassSchema::class)]
    #[Permission(code: 'education:academic:class:page')]
    public function page(ClassPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/classes', operationId: 'educationAcademicClassCreate', summary: 'Create class', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ClassSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class:create')]
    public function create(ClassSaveRequest $request): Result
    {
        return $this->success($this->service->create($request->validated(), $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/classes/{id}', operationId: 'educationAcademicClassUpdate', summary: 'Update class', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ClassSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class:update')]
    public function update(int $id, ClassSaveRequest $request): Result
    {
        return $this->success($this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/classes/{id}/status', operationId: 'educationAcademicClassStatus', summary: 'Class status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ClassStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class:status')]
    public function status(int $id, ClassStatusRequest $request): Result
    {
        $class = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $class->id, 'status' => $class->status]);
    }

    #[Delete(path: '/admin/education/academic/classes/{id}', operationId: 'educationAcademicClassDelete', summary: 'Delete class', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class:delete')]
    public function delete(int $id): Result
    {
        return $this->success($this->service->delete($id, $this->context(), $this->currentUser->id()));
    }

    #[Get(path: '/admin/education/academic/classes/{id}/students', operationId: 'educationAcademicClassStudents', summary: 'Class students', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class-student:page')]
    public function students(int $id): Result
    {
        return $this->success(['list' => $this->studentService->listStudents($id, $this->context())]);
    }

    #[Put(path: '/admin/education/academic/classes/{id}/students', operationId: 'educationAcademicClassSaveStudents', summary: 'Save class students', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ClassStudentSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:class-student:save')]
    public function saveStudents(int $id, ClassStudentSaveRequest $request): Result
    {
        $data = $request->validated();
        $studentIds = $data['student_ids'] ?? array_map(static fn (array $row): int => (int) $row['student_id'], $data['students']);
        $rows = $this->studentService->saveStudents($id, $studentIds, $this->context(), $this->currentUser->id());

        return $this->success(['class_id' => $id, 'active_count' => \count($rows), 'students' => $rows]);
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

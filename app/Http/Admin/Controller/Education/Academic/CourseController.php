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
use App\Http\Admin\Request\Education\Academic\CoursePageRequest;
use App\Http\Admin\Request\Education\Academic\CourseSaveRequest;
use App\Http\Admin\Request\Education\Academic\CourseStatusRequest;
use App\Http\Admin\Request\Education\Academic\CourseTeacherSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\CourseSchema;
use App\Service\Education\Academic\CourseService;
use App\Service\Education\Academic\TeacherCourseService;
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
final class CourseController extends AbstractController
{
    public function __construct(
        private readonly CourseService $service,
        private readonly TeacherCourseService $teacherCourseService,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/courses/page', operationId: 'educationAcademicCoursePage', summary: 'Course page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: CourseSchema::class)]
    #[Permission(code: 'education:academic:course:page')]
    public function page(CoursePageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/courses', operationId: 'educationAcademicCourseCreate', summary: 'Create course', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: CourseSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course:create')]
    public function create(CourseSaveRequest $request): Result
    {
        $course = $this->service->create($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($course->toArray());
    }

    #[Put(path: '/admin/education/academic/courses/{id}', operationId: 'educationAcademicCourseUpdate', summary: 'Update course', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: CourseSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course:update')]
    public function update(int $id, CourseSaveRequest $request): Result
    {
        $course = $this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success($course->toArray());
    }

    #[Put(path: '/admin/education/academic/courses/{id}/status', operationId: 'educationAcademicCourseStatus', summary: 'Course status', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: CourseStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course:status')]
    public function status(int $id, CourseStatusRequest $request): Result
    {
        $course = $this->service->changeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $course->id, 'status' => $course->status]);
    }

    #[Delete(path: '/admin/education/academic/courses/{id}', operationId: 'educationAcademicCourseDelete', summary: 'Delete course', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course:delete')]
    public function delete(int $id): Result
    {
        $this->service->delete($id, $this->context(), $this->currentUser->id());

        return $this->success();
    }

    #[Get(path: '/admin/education/academic/courses/{id}/teachers', operationId: 'educationAcademicCourseTeachers', summary: 'Course teachers', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course-teacher:page')]
    public function teachers(int $id): Result
    {
        return $this->success(['list' => $this->teacherCourseService->listTeachers($id, $this->context())]);
    }

    #[Put(path: '/admin/education/academic/courses/{id}/teachers', operationId: 'educationAcademicCourseSaveTeachers', summary: 'Save course teachers', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: CourseTeacherSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:course-teacher:save')]
    public function saveTeachers(int $id, CourseTeacherSaveRequest $request): Result
    {
        $rows = $this->teacherCourseService->saveTeachers($id, $request->validated()['teacher_ids'], $this->context(), $this->currentUser->id());

        return $this->success(['course_id' => $id, 'teacher_ids' => array_map('intval', array_column($rows, 'teacher_id'))]);
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

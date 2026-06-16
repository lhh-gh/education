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
use App\Http\Admin\Request\Education\Academic\LessonCancelRequest;
use App\Http\Admin\Request\Education\Academic\LessonPageRequest;
use App\Http\Admin\Request\Education\Academic\LessonUpdateRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\LessonSchema;
use App\Service\Education\Academic\LessonService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
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
final class LessonController extends AbstractController
{
    public function __construct(
        private readonly LessonService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/lessons/page', operationId: 'educationAcademicLessonPage', summary: 'Lesson page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: LessonSchema::class)]
    #[Permission(code: 'education:academic:lesson:page')]
    public function page(LessonPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/lessons/{id}', operationId: 'educationAcademicLessonDetail', summary: 'Lesson detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson:detail')]
    public function detail(int $id): Result
    {
        $detail = $this->service->detail($id, $this->context());

        return $this->success($detail['lesson'] + ['students' => $detail['students']]);
    }

    #[Put(path: '/admin/education/academic/lessons/{id}', operationId: 'educationAcademicLessonUpdate', summary: 'Update lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LessonUpdateRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson:update')]
    public function update(int $id, LessonUpdateRequest $request): Result
    {
        return $this->success($this->service->update($id, $request->validated(), $this->context(), $this->currentUser->id())->toArray());
    }

    #[Put(path: '/admin/education/academic/lessons/{id}/cancel', operationId: 'educationAcademicLessonCancel', summary: 'Cancel lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: LessonCancelRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson:cancel')]
    public function cancel(int $id, LessonCancelRequest $request): Result
    {
        return $this->success($this->service->cancel($id, $request->validated()['cancel_reason'], $this->context(), $this->currentUser->id())->toArray());
    }

    #[Delete(path: '/admin/education/academic/lessons/{id}', operationId: 'educationAcademicLessonDelete', summary: 'Delete lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson:delete')]
    public function delete(int $id): Result
    {
        return $this->success($this->service->delete($id, $this->context(), $this->currentUser->id()));
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

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
use App\Http\Admin\Request\Education\Academic\LessonChangePageRequest;
use App\Http\Admin\Request\Education\Academic\MakeupLessonCreateRequest;
use App\Http\Admin\Request\Education\Academic\RescheduleLessonRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Repository\Education\Academic\LessonChangeRepository;
use App\Schema\Education\Academic\LessonChangeSchema;
use App\Schema\Education\Academic\MakeupLessonResultSchema;
use App\Schema\Education\Academic\RescheduleResultSchema;
use App\Service\Education\Academic\MakeupLessonService;
use App\Service\Education\Academic\RescheduleService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class LessonChangeController extends AbstractController
{
    public function __construct(
        private readonly LessonChangeRepository $repository,
        private readonly MakeupLessonService $makeupLessonService,
        private readonly RescheduleService $rescheduleService,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/lesson-changes/page', operationId: 'educationAcademicLessonChangePage', summary: 'Lesson change page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: LessonChangeSchema::class)]
    #[Permission(code: 'education:academic:lesson-change:page')]
    public function page(LessonChangePageRequest $request): Result
    {
        return $this->success($this->repository->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/lesson-changes/{id}', operationId: 'educationAcademicLessonChangeDetail', summary: 'Lesson change detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-change:detail')]
    public function detail(int $id): Result
    {
        $record = $this->repository->findScoped($id, $this->context());
        if ($record === null) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson change not found in current context', ['id' => $id]);
        }

        return $this->success((new LessonChangeSchema($record))->jsonSerialize());
    }

    #[Post(path: '/admin/education/academic/lesson-changes/makeup', operationId: 'educationAcademicLessonChangeMakeup', summary: 'Create make-up lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: MakeupLessonCreateRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-change:makeup')]
    public function makeup(MakeupLessonCreateRequest $request): Result
    {
        return $this->success((new MakeupLessonResultSchema($this->makeupLessonService->create($request->validated(), $this->context(), $this->currentUser->id())))->jsonSerialize());
    }

    #[Post(path: '/admin/education/academic/lesson-changes/reschedule', operationId: 'educationAcademicLessonChangeReschedule', summary: 'Reschedule lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: RescheduleLessonRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-change:reschedule')]
    public function reschedule(RescheduleLessonRequest $request): Result
    {
        return $this->success((new RescheduleResultSchema($this->rescheduleService->reschedule($request->validated(), $this->context(), $this->currentUser->id())))->jsonSerialize());
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

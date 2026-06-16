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
use App\Http\Admin\Request\Education\Academic\BatchLessonScheduleRequest;
use App\Http\Admin\Request\Education\Academic\ScheduleCalendarRequest;
use App\Http\Admin\Request\Education\Academic\ScheduleConflictCheckRequest;
use App\Http\Admin\Request\Education\Academic\SingleLessonScheduleRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Service\Education\Academic\SchedulingService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class LessonScheduleController extends AbstractController
{
    public function __construct(
        private readonly SchedulingService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/lesson-schedule/calendar', operationId: 'educationAcademicLessonScheduleCalendar', summary: 'Lesson calendar', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-schedule:calendar')]
    public function calendar(ScheduleCalendarRequest $request): Result
    {
        return $this->success(['list' => $this->service->calendar($request->validated(), $this->context())]);
    }

    #[Post(path: '/admin/education/academic/lesson-schedule/conflict-check', operationId: 'educationAcademicLessonScheduleConflictCheck', summary: 'Lesson conflict check', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ScheduleConflictCheckRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-schedule:conflict-check')]
    public function conflictCheck(ScheduleConflictCheckRequest $request): Result
    {
        return $this->success($this->service->conflictCheck($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/academic/lesson-schedule/single', operationId: 'educationAcademicLessonScheduleSingle', summary: 'Schedule single lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: SingleLessonScheduleRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-schedule:create')]
    public function single(SingleLessonScheduleRequest $request): Result
    {
        $result = $this->service->scheduleSingle($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success([
            'lesson' => $result['lesson']->toArray(),
            'lesson_students' => ['created_count' => \count($result['students']), 'list' => $result['students']],
        ]);
    }

    #[Post(path: '/admin/education/academic/lesson-schedule/batch', operationId: 'educationAcademicLessonScheduleBatch', summary: 'Schedule batch lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: BatchLessonScheduleRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:lesson-schedule:batch')]
    public function batch(BatchLessonScheduleRequest $request): Result
    {
        $result = $this->service->scheduleBatch($request->validated(), $this->context(), $this->currentUser->id());
        $lessons = $result['lessons'];
        $batchNo = $lessons === [] ? null : $lessons[0]->schedule_batch_no;

        return $this->success([
            'schedule_batch_no' => $batchNo,
            'created_count' => $result['total'],
            'lesson_ids' => array_map(static fn ($lesson): int => (int) $lesson->id, $lessons),
        ]);
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

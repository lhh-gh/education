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

namespace App\Http\Api\Controller\Education\Academic;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Academic\TeacherLessonDetailRequest;
use App\Http\Api\Request\Education\Academic\TeacherLessonPageRequest;
use App\Http\Api\Request\Education\Academic\TeacherLessonTodayRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\TeacherMobileLessonService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherLessonController extends AbstractController
{
    public function __construct(
        private readonly TeacherMobileLessonService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/academic/teacher/lessons/today', operationId: 'educationMobileTeacherLessonToday', summary: 'Teacher today lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function today(TeacherLessonTodayRequest $request): Result
    {
        return $this->success($this->service->today($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/academic/teacher/lessons/page', operationId: 'educationMobileTeacherLessonPage', summary: 'Teacher lesson page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[PageResponse(instance: new Result())]
    public function page(TeacherLessonPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/academic/teacher/lessons/{lessonId}', operationId: 'educationMobileTeacherLessonDetail', summary: 'Teacher lesson detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function detail(int $lessonId, TeacherLessonDetailRequest $request): Result
    {
        return $this->success($this->service->detail($lessonId, $request->validated(), $this->mobileContext->mobile()));
    }
}

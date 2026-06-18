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
use App\Http\Api\Request\Education\Academic\TeacherAttendanceResultRequest;
use App\Http\Api\Request\Education\Academic\TeacherAttendanceSaveRequest;
use App\Http\Api\Request\Education\Academic\TeacherAttendanceSheetRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\TeacherMobileAttendanceService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherAttendanceController extends AbstractController
{
    public function __construct(
        private readonly TeacherMobileAttendanceService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/academic/teacher/lessons/{lessonId}/attendance-sheet', operationId: 'educationMobileTeacherAttendanceSheet', summary: 'Teacher attendance sheet', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function sheet(int $lessonId, TeacherAttendanceSheetRequest $request): Result
    {
        return $this->success($this->service->sheet($lessonId, $request->validated(), $this->mobileContext->mobile()));
    }

    #[Post(path: '/mobile/education/academic/teacher/lessons/{lessonId}/attendance', operationId: 'educationMobileTeacherAttendanceSubmit', summary: 'Teacher attendance submit', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[Middleware(middleware: OperationMiddleware::class, priority: 98)]
    #[RequestBody(content: new JsonContent(ref: TeacherAttendanceSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    public function submit(int $lessonId, TeacherAttendanceSaveRequest $request): Result
    {
        return $this->success($this->service->submit($lessonId, $request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/academic/teacher/lessons/{lessonId}/attendance-result', operationId: 'educationMobileTeacherAttendanceResult', summary: 'Teacher attendance result', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function result(int $lessonId, TeacherAttendanceResultRequest $request): Result
    {
        return $this->success($this->service->result($lessonId, $request->validated(), $this->mobileContext->mobile()));
    }
}

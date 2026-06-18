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

namespace App\Http\Api\Controller\Education\Operations;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Operations\TeacherMakeupAttendanceRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Service\Education\Foundation\MobileContextService;
use App\Service\Education\Operations\MakeupService;
use App\Service\Education\Operations\TeacherWorkloadService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherOperationController extends AbstractController
{
    public function __construct(
        private readonly MobileContextService $mobileContext,
        private readonly MakeupService $makeupService,
        private readonly TeacherWorkloadService $workloadService
    ) {}

    #[Get(path: '/mobile/education/operations/teacher/changed-lessons', operationId: 'educationMobileTeacherChangedLessons', summary: 'Teacher changed lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function changedLessons(): Result
    {
        $context = $this->mobileContext->mobile();
        $lessonIds = EducationLesson::query()
            ->where('tenant_id', $context->tenantId)
            ->where('teacher_id', $context->userId)
            ->pluck('id')
            ->all();
        $rows = EducationLessonChangeRequest::query()
            ->where('tenant_id', $context->tenantId)
            ->whereIn('lesson_id', $lessonIds ?: [0])
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();

        return $this->success(['list' => $rows]);
    }

    #[Post(path: '/mobile/education/operations/teacher/makeup-attendance', operationId: 'educationMobileTeacherMakeupAttendance', summary: 'Teacher makeup attendance', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function makeupAttendance(TeacherMakeupAttendanceRequest $request): Result
    {
        return $this->success($this->makeupService->completeMakeupAttendance((int) $request->validated()['makeup_record_id'], $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/operations/teacher/workload-summary', operationId: 'educationMobileTeacherWorkloadSummary', summary: 'Teacher workload summary', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function workloadSummary(): Result
    {
        $context = $this->mobileContext->mobile();

        return $this->success($this->workloadService->summaryByTeacher($context->tenantId, $context->userId));
    }
}

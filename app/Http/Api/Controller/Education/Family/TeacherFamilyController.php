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

namespace App\Http\Api\Controller\Education\Family;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Family\TeacherGrowthRecordSaveRequest;
use App\Http\Api\Request\Education\Family\TeacherHomeworkReviewRequest;
use App\Http\Api\Request\Education\Family\TeacherLessonCommentSaveRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\GrowthRecordService;
use App\Service\Education\Family\HomeworkReviewService;
use App\Service\Education\Family\LessonCommentService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherFamilyController extends AbstractController
{
    public function __construct(
        private readonly LessonCommentService $commentService,
        private readonly HomeworkReviewService $reviewService,
        private readonly GrowthRecordService $growthRecordService,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Post(path: '/mobile/education/family/teacher/lesson-comments', operationId: 'educationMobileFamilyTeacherCommentSave', summary: 'Teacher family comment save', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function saveLessonComment(TeacherLessonCommentSaveRequest $request): Result
    {
        return $this->success($this->commentService->saveTeacherComment($request->validated(), $this->mobileContext->mobile()));
    }

    #[Post(path: '/mobile/education/family/teacher/homework-reviews', operationId: 'educationMobileFamilyTeacherHomeworkReview', summary: 'Teacher homework review', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function reviewHomework(TeacherHomeworkReviewRequest $request): Result
    {
        return $this->success($this->reviewService->review($request->validated(), $this->mobileContext->mobile()));
    }

    #[Post(path: '/mobile/education/family/teacher/growth-records', operationId: 'educationMobileFamilyTeacherGrowthSave', summary: 'Teacher growth record save', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function saveGrowthRecord(TeacherGrowthRecordSaveRequest $request): Result
    {
        return $this->success($this->growthRecordService->saveByTeacher($request->validated(), $this->mobileContext->mobile()));
    }
}

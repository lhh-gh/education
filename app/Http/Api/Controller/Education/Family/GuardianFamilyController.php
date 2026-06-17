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
use App\Http\Api\Request\Education\Family\FamilyMessageSendRequest;
use App\Http\Api\Request\Education\Family\GuardianHomeworkSubmissionRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\FamilyMessageService;
use App\Service\Education\Family\GrowthRecordService;
use App\Service\Education\Family\HomeworkService;
use App\Service\Education\Family\LearningReportService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianFamilyController extends AbstractController
{
    public function __construct(
        private readonly HomeworkService $homeworkService,
        private readonly LearningReportService $learningReportService,
        private readonly GrowthRecordService $growthRecordService,
        private readonly FamilyMessageService $messageService,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Post(path: '/mobile/education/family/guardian/homework-submissions', operationId: 'educationMobileFamilyGuardianHomeworkSubmit', summary: 'Guardian homework submit', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function submitHomework(GuardianHomeworkSubmissionRequest $request): Result
    {
        return $this->success($this->homeworkService->submitByGuardian($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/family/guardian/students/{studentId}/learning-reports', operationId: 'educationMobileFamilyGuardianReports', summary: 'Guardian family reports', tags: ['Education Mobile Family'])]
    #[PageResponse(instance: new Result())]
    public function learningReports(int $studentId): Result
    {
        return $this->success($this->learningReportService->visibleForGuardian($studentId, [], $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/family/guardian/students/{studentId}/growth-records', operationId: 'educationMobileFamilyGuardianGrowthRecords', summary: 'Guardian family growth records', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function growthRecords(int $studentId): Result
    {
        return $this->success($this->growthRecordService->visibleForGuardian($studentId, $this->mobileContext->mobile()));
    }

    #[Post(path: '/mobile/education/family/messages', operationId: 'educationMobileFamilyMessageSend', summary: 'Family message send', tags: ['Education Mobile Family'])]
    #[ResultResponse(instance: new Result())]
    public function sendMessage(FamilyMessageSendRequest $request): Result
    {
        return $this->success($this->messageService->send($request->validated(), $this->mobileContext->mobile()));
    }
}

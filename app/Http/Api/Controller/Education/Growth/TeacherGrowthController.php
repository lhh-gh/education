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

namespace App\Http\Api\Controller\Education\Growth;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Growth\TeacherTrialFeedbackRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Foundation\MobileContextService;
use App\Service\Education\Growth\TrialConversionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherGrowthController extends AbstractController
{
    public function __construct(
        private readonly TrialConversionService $service,
        private readonly MobileContextService $mobileContext,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Post(path: '/mobile/education/growth/teacher/trial-feedback', operationId: 'educationMobileGrowthTeacherTrialFeedback', summary: 'Mobile growth teacher trial feedback', tags: ['Education Mobile Growth'])]
    #[ResultResponse(instance: new Result())]
    public function submitTrialFeedback(TeacherTrialFeedbackRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $result = $this->service->submitTeacherFeedback($context->userId, $request->validated());
        $this->events->dispatch(new EducationAuditEvent(
            module: 'growth',
            resource: 'trial_feedback',
            action: 'education.growth.trial_feedback.submitted',
            businessType: 'trial_feedback',
            businessId: $result['trial_feedback_id'],
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $result,
            metadata: ['tenant_id' => $context->tenantId, 'campus_id' => $context->currentCampusId],
            summary: 'education.growth.trial_feedback.submitted',
            actorType: 'teacher'
        ));

        return $this->success($result);
    }
}

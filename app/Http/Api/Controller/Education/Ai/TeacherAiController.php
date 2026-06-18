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

namespace App\Http\Api\Controller\Education\Ai;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Ai\TeacherCommentDraftRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiGenerationService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherAiController extends AbstractController
{
    public function __construct(
        private readonly AiGenerationService $generationService,
        private readonly MobileContextService $mobileContext,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Post(path: '/mobile/education/ai/teacher/lesson-comment-drafts', operationId: 'educationMobileAiTeacherCommentDraft', summary: 'Teacher AI comment draft request', tags: ['Education Mobile AI'])]
    #[ResultResponse(instance: new Result())]
    public function requestLessonCommentDraft(TeacherCommentDraftRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $result = $this->generationService->requestLessonCommentDraft($request->validated(), $context);
        $this->events->dispatch(new EducationAuditEvent(
            module: 'ai',
            resource: 'generation_task',
            action: 'education.ai.generation.requested',
            businessType: 'generation_task',
            businessId: $result['task_id'],
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $result,
            metadata: ['tenant_id' => $context->tenantId, 'campus_id' => $context->currentCampusId],
            summary: 'education.ai.generation.requested',
            actorType: 'teacher'
        ));

        return $this->success($result);
    }
}

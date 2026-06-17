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

namespace App\Http\Api\Controller\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Admissions\TeacherTrialFeedbackRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Admissions\TrialFeedbackService;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherTrialController extends AbstractController
{
    public function __construct(
        private readonly MobileContextService $mobileContext,
        private readonly TrialFeedbackService $feedbackService
    ) {}

    #[Get(path: '/mobile/education/admissions/teacher/trial-lessons', operationId: 'educationMobileTeacherTrialLessons', summary: 'Teacher trial lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Admissions'])]
    #[ResultResponse(instance: new Result())]
    public function lessons(RequestInterface $request): Result
    {
        $context = $this->teacherContext();
        $query = EducationTrialLesson::query()
            ->where('tenant_id', $context->tenantId)
            ->where('teacher_id', $context->userId);
        $date = (string) $request->input('date', '');
        if ($date !== '') {
            $query->where('start_time', '>=', $date . ' 00:00:00')->where('start_time', '<=', $date . ' 23:59:59');
        }

        return $this->success(['list' => $query->orderBy('start_time')->get()->toArray()]);
    }

    #[Post(path: '/mobile/education/admissions/teacher/trial-feedbacks', operationId: 'educationMobileTeacherTrialFeedbackCreate', summary: 'Teacher trial feedback', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Admissions'])]
    #[ResultResponse(instance: new Result())]
    public function feedback(TeacherTrialFeedbackRequest $request): Result
    {
        $context = $this->teacherContext();
        $data = $request->validated();
        $lesson = EducationTrialLesson::query()
            ->where('tenant_id', $context->tenantId)
            ->whereKey((int) $data['trial_lesson_id'])
            ->first();
        if (! $lesson instanceof EducationTrialLesson || (int) $lesson->teacher_id !== $context->userId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'trial lesson is not assigned to current teacher', ['trial_lesson_id' => (int) $data['trial_lesson_id']]);
        }

        return $this->success($this->feedbackService->create([
            'trial_lesson_id' => (int) $data['trial_lesson_id'],
            'feedback_type' => 'teacher',
            'teacher_id' => $context->userId,
            'score' => $data['score'] ?? null,
            'content' => $data['content'],
            'recommend_course_id' => $data['recommend_course_id'] ?? null,
        ], $context));
    }

    private function teacherContext(): EducationUserContext
    {
        $context = $this->mobileContext->mobile();
        if ($context->roleCode !== EducationRoleCode::Teacher) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'current user has no teacher profile', []);
        }

        return $context;
    }
}

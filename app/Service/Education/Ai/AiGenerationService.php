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

namespace App\Service\Education\Ai;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Ai\EducationAiGenerationTask;
use App\Repository\Education\Ai\AiConfigRepository;
use App\Repository\Education\Ai\AiGenerationRepository;
use App\Repository\Education\Ai\AiUsageRepository;
use App\Repository\Education\Ai\PromptTemplateRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class AiGenerationService
{
    public function __construct(
        private readonly AiContextBuilderService $contextBuilder,
        private readonly AiConfigRepository $configRepository,
        private readonly PromptTemplateRepository $promptRepository,
        private readonly AiGenerationRepository $generationRepository,
        private readonly AiUsageRepository $usageRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{task_id: int, status: string}
     */
    public function requestLessonCommentDraft(array $data, EducationUserContext $context): array
    {
        $tenantId = (int) $context->tenantId;
        $feature = $this->configRepository->enabledFeature($tenantId, 'lesson_comment');
        $prompt = $this->promptRepository->publishedForFeature($tenantId, 'lesson_comment');
        if ($feature === null || $prompt === null) {
            throw new BusinessException(ResultCode::CONFLICT, 'ai feature is not configured', ['feature_code' => 'lesson_comment']);
        }

        $lessonContext = $this->contextBuilder->teacherLessonStudentContext((int) $data['lesson_id'], (int) $data['student_id'], $context);
        $task = $this->generationRepository->createTask([
            'tenant_id' => $tenantId,
            'campus_id' => $context->currentCampusId,
            'task_no' => uniqid('AIT', true),
            'feature_code' => 'lesson_comment',
            'model_config_id' => (int) $feature->model_config_id,
            'prompt_template_id' => (int) $prompt->id,
            'business_type' => 'lesson_student',
            'business_id' => ((int) $data['lesson_id'] * 100000000) + (int) $data['student_id'],
            'requester_user_id' => $context->userId,
            'status' => 'queued',
            'context_hash' => $lessonContext['context_hash'],
            'queued_at' => Carbon::now(),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['task_id' => (int) $task->id, 'status' => 'queued'];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{task_id: int, status: string}
     */
    public function requestAdminGenerationTask(array $data, EducationUserContext $context): array
    {
        $tenantId = (int) $context->tenantId;
        $featureCode = (string) $data['feature_code'];
        $feature = $this->configRepository->enabledFeature($tenantId, $featureCode);
        $prompt = $this->promptRepository->publishedForFeature($tenantId, $featureCode);
        if ($feature === null || $prompt === null) {
            throw new BusinessException(ResultCode::CONFLICT, 'ai feature is not configured', ['feature_code' => $featureCode]);
        }

        $task = $this->generationRepository->createTask([
            'tenant_id' => $tenantId,
            'campus_id' => $context->currentCampusId,
            'task_no' => uniqid('AIT', true),
            'feature_code' => $featureCode,
            'model_config_id' => (int) $feature->model_config_id,
            'prompt_template_id' => (int) $prompt->id,
            'business_type' => (string) $data['business_type'],
            'business_id' => $data['business_id'] ?? null,
            'requester_user_id' => $context->userId,
            'status' => 'queued',
            'context_hash' => hash('sha256', json_encode($data, \JSON_THROW_ON_ERROR)),
            'queued_at' => Carbon::now(),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['task_id' => (int) $task->id, 'status' => 'queued'];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTasks(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiGenerationTask::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array<string, mixed>
     */
    public function resultDetail(int $id): array
    {
        return $this->generationRepository->result($id)->toArray();
    }

    /**
     * @param null|array<string, mixed> $resultJson
     * @return array{generation_result_id: int, review_status: string}
     */
    public function storeSuccessfulResult(int $taskId, string $text, ?array $resultJson = null, int $promptTokens = 0, int $completionTokens = 0, int $costCents = 0): array
    {
        $task = $this->generationRepository->task($taskId);
        $task->status = 'succeeded';
        $task->finished_at = Carbon::now();
        $task->save();
        $result = $this->generationRepository->createResult([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'generation_task_id' => $taskId,
            'result_text' => $text,
            'result_json' => $resultJson,
            'safety_status' => 'normal',
            'review_status' => 'pending',
            'visible_to_guardian' => false,
            'created_by' => $task->requester_user_id,
            'updated_by' => $task->requester_user_id,
        ]);
        $this->usageRepository->create([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'generation_task_id' => $taskId,
            'provider' => 'internal',
            'model_name' => 'queued-adapter',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_cents' => $costCents,
            'usage_date' => Carbon::now()->toDateString(),
        ]);

        return ['generation_result_id' => (int) $result->id, 'review_status' => 'pending'];
    }

    /**
     * @return array{generation_result_id: int, safety_status: string}
     */
    public function storeBlockedResult(int $taskId, string $text): array
    {
        $task = $this->generationRepository->task($taskId);
        $task->status = 'blocked';
        $task->finished_at = Carbon::now();
        $task->save();
        $result = $this->generationRepository->createResult([
            'tenant_id' => (int) $task->tenant_id,
            'campus_id' => $task->campus_id,
            'generation_task_id' => $taskId,
            'result_text' => $text,
            'safety_status' => 'blocked',
            'review_status' => 'pending',
            'visible_to_guardian' => false,
            'created_by' => $task->requester_user_id,
            'updated_by' => $task->requester_user_id,
        ]);

        return ['generation_result_id' => (int) $result->id, 'safety_status' => 'blocked'];
    }
}

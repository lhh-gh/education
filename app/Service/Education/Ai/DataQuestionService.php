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
use App\Model\Education\Ai\EducationAiDataQuestionLog;
use App\Model\Education\Ai\EducationAiMetricCatalog;
use App\Repository\Education\Ai\AiMetricCatalogRepository;
use App\Repository\Education\Ai\DataQuestionRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class DataQuestionService
{
    public function __construct(
        private readonly DataQuestionRepository $questionRepository,
        private readonly AiMetricCatalogRepository $metricRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{question_log_id: int, status: string, answer_text: string}
     */
    public function ask(array $data, EducationUserContext $context): array
    {
        $question = (string) ($data['question_text'] ?? '');
        if (preg_match('/\b(select|insert|update|delete|drop|alter|truncate)\b/i', $question) === 1) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'raw SQL is not allowed for data question', []);
        }

        $metricCodes = array_map('strval', (array) ($data['metric_codes'] ?? []));
        foreach ($metricCodes as $metricCode) {
            $metric = $this->metricRepository->findEnabledByCode((int) $context->tenantId, $metricCode);
            if ($metric === null) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'metric is not allowed for current role', ['metric_code' => $metricCode]);
            }
            $roles = (array) $metric->allowed_roles_json;
            if (! $context->platformAccess && ! \in_array($context->roleCode->value, $roles, true)) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'metric is not allowed for current role', ['metric_code' => $metricCode]);
            }
        }

        $answer = 'Metrics: ' . implode(', ', $metricCodes);
        $log = $this->questionRepository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => $context->currentCampusId,
            'question_text' => $question,
            'metric_codes_json' => $metricCodes,
            'answer_text' => $answer,
            'requester_user_id' => $context->userId,
            'status' => 'succeeded',
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['question_log_id' => (int) $log->id, 'status' => 'succeeded', 'answer_text' => $answer];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageLogs(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiDataQuestionLog::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageMetricCatalogs(int $tenantId, int $page = 1, int $pageSize = 20): array
    {
        $query = EducationAiMetricCatalog::query()->where('tenant_id', $tenantId);
        $total = (int) $query->count();
        $list = $query->orderBy('metric_group')->orderBy('metric_code')->forPage($page, $pageSize)->get()->toArray();

        return ['list' => $list, 'total' => $total];
    }
}

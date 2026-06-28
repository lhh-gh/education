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

namespace App\Service\Education\Family;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Family\EducationLearningReport;
use App\Repository\Education\Family\HomeworkRepository;
use App\Repository\Education\Family\LearningReportRepository;
use App\Repository\Education\Family\ReadReceiptRepository;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class LearningReportService
{
    public function __construct(
        private readonly LearningReportRepository $repository,
        private readonly HomeworkRepository $homeworkRepository,
        private readonly GuardianMobileContextResolver $guardianResolver,
        private readonly ReadReceiptRepository $readReceiptRepository,
        private readonly ServiceQualityService $qualityService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{learning_report_id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
            $payload = [
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => (int) ($data['campus_id'] ?? $context->currentCampusId),
                'student_id' => (int) ($data['student_id'] ?? 0),
                'report_title' => (string) ($data['report_title'] ?? ''),
                'report_period' => (string) ($data['report_period'] ?? ''),
                'summary' => $data['summary'] ?? null,
                'updated_by' => $context->userId,
            ];
            if ($id === null) {
                $report = $this->repository->create($payload + [
                    'status' => 'draft',
                    'created_by' => $context->userId,
                ]);
            } else {
                $report = $this->mustFind($id, $context);
                $report = $this->repository->update($report, $payload);
            }
            $this->repository->replaceItems($report, (array) ($data['items'] ?? []));

            return ['learning_report_id' => (int) $report->id, 'status' => $this->statusValue($report)];
        });
    }

    /**
     * @return array{learning_report_id: int, status: string}
     */
    public function publish(int $id, EducationUserContext $context): array
    {
        $report = $this->mustFind($id, $context);
        if ($this->repository->itemCount($id, (int) $context->tenantId) === 0) {
            throw new BusinessException(ResultCode::CONFLICT, 'learning report has no report items', ['learning_report_id' => $id]);
        }
        $report = $this->repository->update($report, [
            'status' => 'published',
            'published_at' => Carbon::now(),
            'withdrawn_at' => null,
            'updated_by' => $context->userId,
        ]);
        $this->qualityService->incrementReport((int) $context->tenantId, $report->campus_id, (int) $report->student_id);

        return ['learning_report_id' => (int) $report->id, 'status' => $this->statusValue($report)];
    }

    /**
     * @return array{learning_report_id: int, status: string}
     */
    public function withdraw(int $id, EducationUserContext $context): array
    {
        $report = $this->mustFind($id, $context);
        $report = $this->repository->update($report, [
            'status' => 'withdrawn',
            'withdrawn_at' => Carbon::now(),
            'updated_by' => $context->userId,
        ]);

        return ['learning_report_id' => (int) $report->id, 'status' => $this->statusValue($report)];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function visibleForGuardian(int $studentId, array $filters, EducationUserContext $context): array
    {
        $guardian = $this->guardianResolver->resolveGuardian($context);
        if (! $this->homeworkRepository->isGuardianBound((int) $context->tenantId, $studentId, (int) $guardian->id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }

        $result = $this->repository->visibleForGuardian($studentId, $filters, $context);
        foreach ($result['list'] as $report) {
            $this->readReceiptRepository->createOnce(
                (int) $context->tenantId,
                isset($report['campus_id']) ? (int) $report['campus_id'] : null,
                'learning_report',
                (int) $report['id'],
                $studentId,
                'guardian',
                $context->userId
            );
        }

        return $result;
    }

    private function mustFind(int $id, EducationUserContext $context): EducationLearningReport
    {
        $report = $this->repository->find($id, $context);
        if (! $report instanceof EducationLearningReport) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'learning report not found', ['learning_report_id' => $id]);
        }

        return $report;
    }

    private function statusValue(EducationLearningReport $report): string
    {
        return $report->status instanceof \BackedEnum ? (string) $report->status->value : (string) $report->status;
    }
}

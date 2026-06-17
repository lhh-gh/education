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

namespace App\Service\Education\Group;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Group\EducationContract;
use App\Repository\Education\Group\ContractRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class ContractService
{
    public function __construct(
        private readonly ContractRepository $repository,
        private readonly RiskAuditService $riskAuditService,
        private readonly DataPermissionService $dataPermissionService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{contract_id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
            $contractNo = (string) ($data['contract_no'] ?? '');
            if ($contractNo === '') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'contract_no is required', ['field' => 'contract_no']);
            }
            $existingByNo = $this->repository->findByNo((int) $context->tenantId, $contractNo);
            if ($id === null && $existingByNo instanceof EducationContract) {
                throw new BusinessException(ResultCode::CONFLICT, 'contract no already exists', ['contract_no' => $contractNo]);
            }
            $payload = [
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId,
                'contract_no' => $contractNo,
                'contract_type' => (string) ($data['contract_type'] ?? ''),
                'title' => (string) ($data['title'] ?? ''),
                'counterparty_name' => (string) ($data['counterparty_name'] ?? ''),
                'amount_cents' => (int) ($data['amount_cents'] ?? 0),
                'status' => (string) ($data['status'] ?? 'draft'),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'owner_user_id' => isset($data['owner_user_id']) ? (int) $data['owner_user_id'] : null,
                'risk_level' => (string) ($data['risk_level'] ?? 'normal'),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ];
            if ($id === null) {
                $contract = $this->repository->create($payload);
            } else {
                $contract = $this->mustFind($id, $context);
                $this->recordActiveContractRisks($contract, $payload, $context);
                $contract = $this->repository->update($contract, $payload);
            }

            return ['contract_id' => (int) $contract->id, 'status' => (string) $contract->status];
        });
    }

    /**
     * @return array{contract_id: int, status: string}
     */
    public function submitReview(int $id, EducationUserContext $context): array
    {
        $contract = $this->mustFind($id, $context);
        $contract = $this->repository->update($contract, ['status' => 'reviewing', 'updated_by' => $context->userId]);

        return ['contract_id' => (int) $contract->id, 'status' => (string) $contract->status];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context, $this->dataPermissionService->allowedCampusIds($context));
    }

    private function mustFind(int $id, EducationUserContext $context): EducationContract
    {
        $contract = $this->repository->find($id, $context);
        if (! $contract instanceof EducationContract) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'contract not found', ['contract_id' => $id]);
        }

        return $contract;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recordActiveContractRisks(EducationContract $contract, array $payload, EducationUserContext $context): void
    {
        if ($contract->status !== 'active') {
            return;
        }
        if ((int) $contract->amount_cents !== (int) $payload['amount_cents']) {
            $this->riskAuditService->record([
                'campus_id' => $payload['campus_id'],
                'event_type' => 'contract_amount_changed',
                'risk_level' => 'high',
                'business_type' => 'contract',
                'business_id' => (int) $contract->id,
                'summary' => 'Active contract amount changed',
                'payload_json' => ['before' => (int) $contract->amount_cents, 'after' => (int) $payload['amount_cents']],
            ], $context);
        }
    }
}

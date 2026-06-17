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

namespace App\Service\Education\Finance;

use App\Model\Education\Finance\EducationPaymentRecord;
use App\Model\Enums\Education\Finance\ReconciliationStatus;
use App\Repository\Education\Finance\PaymentRecordRepository;
use App\Repository\Education\Finance\ReconciliationRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class ReconciliationService
{
    public function __construct(
        private readonly ReconciliationRepository $repository,
        private readonly PaymentRecordRepository $paymentRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{batch_id: int, total_count: int, matched_count: int, unmatched_count: int}
     */
    public function import(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $rows = \is_array($data['rows'] ?? null) ? $data['rows'] : [];
            $tenantId = (int) $context->tenantId;
            $campusId = $context->currentCampusId;
            $batch = $this->repository->createBatch([
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'batch_no' => $this->repository->nextBatchNo($tenantId, $campusId),
                'channel_code' => (string) ($data['channel_code'] ?? 'wechat'),
                'business_date' => (string) ($data['business_date'] ?? date('Y-m-d')),
                'status' => ReconciliationStatus::Imported->value,
                'total_count' => \count($rows),
                'matched_count' => 0,
                'unmatched_count' => 0,
                'total_amount_cents' => 0,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            $matched = 0;
            $unmatched = 0;
            $totalAmount = 0;
            foreach ($rows as $row) {
                if (! \is_array($row)) {
                    continue;
                }
                $amount = (int) ($row['amount_cents'] ?? 0);
                $totalAmount += $amount;
                $payment = $this->paymentRepository->findByTradeNo((string) $data['channel_code'], (string) ($row['channel_trade_no'] ?? ''));
                $isMatched = $payment instanceof EducationPaymentRecord && (int) $payment->amount_cents === $amount;
                $isMatched ? ++$matched : ++$unmatched;
                $this->repository->createItem([
                    'tenant_id' => $tenantId,
                    'campus_id' => $campusId,
                    'batch_id' => (int) $batch->id,
                    'channel_trade_no' => (string) ($row['channel_trade_no'] ?? ''),
                    'payment_record_id' => $isMatched ? (int) $payment->id : null,
                    'amount_cents' => $amount,
                    'trade_time' => $row['trade_time'] ?? null,
                    'match_status' => $isMatched ? 'matched' : 'unmatched',
                    'difference_cents' => $isMatched ? 0 : $amount,
                    'raw_row_json' => $row,
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
            }

            $batch->fill([
                'status' => $unmatched === 0 ? ReconciliationStatus::Matched->value : ReconciliationStatus::PartiallyMatched->value,
                'matched_count' => $matched,
                'unmatched_count' => $unmatched,
                'total_amount_cents' => $totalAmount,
                'updated_by' => $context->userId,
            ]);
            $batch->save();

            return [
                'batch_id' => (int) $batch->id,
                'total_count' => (int) $batch->total_count,
                'matched_count' => $matched,
                'unmatched_count' => $unmatched,
            ];
        });
    }
}

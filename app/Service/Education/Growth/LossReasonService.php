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

namespace App\Service\Education\Growth;

use App\Repository\Education\Growth\LossReasonRepository;

final class LossReasonService
{
    public function __construct(private readonly LossReasonRepository $lossReasons) {}

    /**
     * @param array<string, mixed> $data
     * @return array{reason_id: int, status: string}
     */
    public function saveReason(array $data): array
    {
        $reason = $this->lossReasons->saveReason($data + ['status' => 'enabled']);

        return ['reason_id' => (int) $reason->id, 'status' => (string) $reason->status];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{lead_id: int, status: string}
     */
    public function createLossRecord(array $data): array
    {
        $record = $this->lossReasons->createLossRecord($data);

        return ['lead_id' => (int) $record->lead_id, 'status' => 'lost'];
    }
}

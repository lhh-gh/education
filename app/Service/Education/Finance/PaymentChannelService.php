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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Repository\Education\Finance\PaymentChannelRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class PaymentChannelService
{
    public function __construct(
        private readonly PaymentChannelRepository $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function list(array $filters, EducationUserContext $context): array
    {
        return $this->repository->list($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function save(array $data, EducationUserContext $context): array
    {
        if ($context->tenantId === null && ! $context->platformAccess) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
        }
        $tenantId = $context->platformAccess ? (int) ($data['tenant_id'] ?? 0) : (int) $context->tenantId;
        $channel = $this->repository->save([
            'tenant_id' => $tenantId,
            'campus_id' => isset($data['campus_id']) && $data['campus_id'] !== '' ? (int) $data['campus_id'] : null,
            'channel_code' => (string) $data['channel_code'],
            'channel_name' => (string) $data['channel_name'],
            'channel_type' => (string) $data['channel_type'],
            'config_json' => $data['config_json'] ?? [],
            'status' => (string) ($data['status'] ?? 'enabled'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $channel->toArray();
    }
}

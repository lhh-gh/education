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

namespace App\Service\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Enums\Education\Foundation\ConfigOwnerType;

final class ConfigOwnerResolver
{
    public function systemOwnerKey(): string
    {
        return ConfigOwnerType::System->value;
    }

    public function tenantOwnerKey(int $tenantId): string
    {
        return 'tenant:' . $tenantId;
    }

    public function resolveOwnerKey(string $ownerType, ?int $tenantId): string
    {
        $ownerType = $this->normalizeOwnerType($ownerType);
        if ($ownerType === ConfigOwnerType::System->value) {
            if ($tenantId !== null) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'system owner requires empty tenant_id');
            }

            return $this->systemOwnerKey();
        }

        if ($tenantId === null || $tenantId <= 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'tenant owner requires tenant_id');
        }

        return $this->tenantOwnerKey($tenantId);
    }

    public function assertCanWriteOwner(EducationUserContext $context, string $ownerType, ?int $tenantId): void
    {
        $ownerType = $this->normalizeOwnerType($ownerType);
        $this->resolveOwnerKey($ownerType, $tenantId);

        if ($context->platformAccess) {
            return;
        }

        if ($ownerType === ConfigOwnerType::System->value) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant context cannot write system configuration');
        }

        if ($context->tenantId === null || (int) $context->tenantId !== (int) $tenantId) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'tenant is outside current user scope',
                ['tenant_id' => $tenantId]
            );
        }
    }

    private function normalizeOwnerType(string $ownerType): string
    {
        if (ConfigOwnerType::tryFrom($ownerType) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid config owner type');
        }

        return ConfigOwnerType::from($ownerType)->value;
    }
}

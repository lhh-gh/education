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

use App\Model\Enums\Education\Foundation\EducationRoleCode;

final class EducationScopeQuery
{
    /**
     * @param array<string, mixed> $filters
     */
    public function applyTenantCampus(mixed $query, array $filters, EducationUserContext $context): mixed
    {
        $tenantId = $this->tenantId($filters, $context);
        if ($tenantId === null && ! $context->platformAccess) {
            return $query->whereRaw('1 = 0');
        }
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $campusId = $this->campusId($filters, $context);
        if ($campusId !== null) {
            if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('campus_id', $campusId);
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin) {
            return $query;
        }

        return $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function tenantId(array $filters, EducationUserContext $context): ?int
    {
        if (! $context->platformAccess) {
            return $context->tenantId;
        }

        if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
            return (int) $filters['tenant_id'];
        }

        return $context->tenantId;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function campusId(array $filters, EducationUserContext $context): ?int
    {
        if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
            return (int) $filters['campus_id'];
        }

        return $context->currentCampusId;
    }
}

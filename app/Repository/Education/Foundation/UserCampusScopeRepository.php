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

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Repository\IRepository;

/**
 * @extends IRepository<EducationUserCampusScope>
 */
final class UserCampusScopeRepository extends IRepository
{
    public function __construct(
        protected readonly EducationUserCampusScope $model
    ) {}

    /**
     * @return int[]
     */
    public function campusIdsForUser(int $tenantId, int $userId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('campus_id')
            ->pluck('campus_id')
            ->map(static fn ($campusId) => (int) $campusId)
            ->all();
    }

    /**
     * @return int[]
     */
    public function campusIdsForProfile(int $tenantId, int $profileId): array
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('user_profile_id', $profileId)
            ->orderBy('campus_id')
            ->pluck('campus_id')
            ->map(static fn ($campusId) => (int) $campusId)
            ->all();
    }

    /**
     * @param int[] $campusIds
     */
    public function replaceScopes(int $tenantId, int $profileId, int $userId, array $campusIds, ?int $operatorId): void
    {
        $this->deleteByProfile($tenantId, $profileId);

        foreach ($campusIds as $campusId) {
            $this->create([
                'tenant_id' => $tenantId,
                'user_profile_id' => $profileId,
                'user_id' => $userId,
                'campus_id' => $campusId,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);
        }
    }

    public function deleteByProfile(int $tenantId, int $profileId): int
    {
        return $this->getQuery()
            ->where('tenant_id', $tenantId)
            ->where('user_profile_id', $profileId)
            ->delete();
    }
}

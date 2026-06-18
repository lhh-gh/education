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

final readonly class EducationUserContext
{
    /**
     * @param int[] $campusIds
     */
    public function __construct(
        public int $userId,
        public ?int $tenantId,
        public EducationRoleCode $roleCode,
        public bool $platformAccess,
        public array $campusIds,
        public ?int $currentCampusId
    ) {}

    public function canAccessCampus(int $campusId): bool
    {
        return $this->platformAccess || \in_array($campusId, $this->campusIds, true);
    }
}

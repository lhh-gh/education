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

namespace App\Schema\Education\Foundation;

use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;

final class MobileContextSchema
{
    /**
     * @param array<int, array{campus_id:int, campus_name:string}> $campusScopes
     * @param array<string, bool> $featureFlags
     * @param array<int, array{key:string, label:string, path:string}> $tabs
     */
    public function context(
        EducationTenant $tenant,
        EducationUserProfile $profile,
        array $campusScopes,
        array $featureFlags,
        string $defaultPath,
        array $tabs,
        ?array $emptyState = null,
        ?int $selectedCampusId = null
    ): array {
        $currentCampusId = $selectedCampusId ?? $profile->current_campus_id;

        return [
            'tenant' => [
                'id' => (int) $tenant->id,
                'name' => (string) $tenant->name,
                'short_name' => $tenant->short_name,
            ],
            'profile' => [
                'id' => (int) $profile->id,
                'user_id' => (int) $profile->user_id,
                'role_code' => $this->roleCodeValue($profile->role_code),
                'display_name' => (string) $profile->display_name,
                'mobile' => $profile->mobile,
                'avatar' => $profile->avatar,
                'current_campus_id' => $profile->current_campus_id === null ? null : (int) $profile->current_campus_id,
            ],
            'campus_scopes' => array_map(static fn (array $campus): array => [
                'campus_id' => (int) $campus['campus_id'],
                'campus_name' => (string) $campus['campus_name'],
                'is_current' => $currentCampusId !== null && (int) $campus['campus_id'] === (int) $currentCampusId,
            ], $campusScopes),
            'feature_flags' => $featureFlags,
            'entry' => [
                'default_path' => $defaultPath,
                'tabs' => $tabs,
            ],
            'empty_state' => $emptyState,
        ];
    }

    private function roleCodeValue(mixed $roleCode): string
    {
        return $roleCode instanceof EducationRoleCode ? $roleCode->value : (string) $roleCode;
    }
}

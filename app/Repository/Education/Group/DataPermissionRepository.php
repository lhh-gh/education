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

namespace App\Repository\Education\Group;

use App\Model\Education\Group\EducationDataPermissionScope;
use App\Model\Education\Group\EducationUserDataPermission;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class DataPermissionRepository
{
    public function findScopeByCode(int $tenantId, string $code): ?EducationDataPermissionScope
    {
        $row = EducationDataPermissionScope::query()->where('tenant_id', $tenantId)->where('scope_code', $code)->first();

        return $row instanceof EducationDataPermissionScope ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveScope(array $data): EducationDataPermissionScope
    {
        return EducationDataPermissionScope::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'scope_code' => $data['scope_code']],
            $data
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveUserPermission(array $data): EducationUserDataPermission
    {
        return EducationUserDataPermission::query()->updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'user_id' => $data['user_id'], 'scope_id' => $data['scope_id']],
            $data
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function userScopes(int $userId, EducationUserContext $context): array
    {
        $rows = Db::table('edu_user_data_permissions as udp')
            ->join('edu_data_permission_scopes as dps', 'dps.id', '=', 'udp.scope_id')
            ->where('udp.tenant_id', $context->tenantId)
            ->where('udp.user_id', $userId)
            ->where('udp.status', 'enabled')
            ->whereNull('udp.deleted_at')
            ->where('dps.status', 'enabled')
            ->whereNull('dps.deleted_at')
            ->select(['udp.scope_type', 'dps.scope_value_json'])
            ->get();
        $scopes = [];
        foreach ($rows as $row) {
            $scopes[] = [
                'scope_type' => (string) $row->scope_type,
                'scope_value_json' => \is_string($row->scope_value_json) ? json_decode($row->scope_value_json, true) : $row->scope_value_json,
            ];
        }

        return $scopes;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        $query = (new EducationScopeQuery())->applyTenantCampusColumns(
            EducationUserDataPermission::query(),
            $filters,
            $context,
            campusScoped: false
        );
        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('user_id', (int) $filters['user_id']);
        }
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationUserDataPermission $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}

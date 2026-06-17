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
use App\Repository\Education\Group\DataPermissionRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class DataPermissionService
{
    public function __construct(private readonly DataPermissionRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{user_id: int, scope_type: string, allowed_campus_ids: array<int, int>}
     */
    public function saveUserPermission(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $scopeType = (string) ($data['scope_type'] ?? '');
            if (! \in_array($scopeType, ['group_all', 'org_tree', 'campus_set', 'self'], true)) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'scope_type has an invalid value', ['field' => 'scope_type']);
            }
            $campusIds = array_values(array_map('intval', $data['campus_ids'] ?? []));
            $scope = $this->repository->saveScope([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'scope_code' => (string) ($data['scope_code'] ?? ('USER_' . (int) $data['user_id'] . '_' . $scopeType)),
                'scope_name' => (string) ($data['scope_name'] ?? $scopeType),
                'scope_type' => $scopeType,
                'scope_value_json' => ['campus_ids' => $campusIds, 'org_unit_ids' => array_values(array_map('intval', $data['org_unit_ids'] ?? []))],
                'status' => (string) ($data['status'] ?? 'enabled'),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $this->repository->saveUserPermission([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'user_id' => (int) $data['user_id'],
                'scope_id' => (int) $scope->id,
                'scope_type' => $scopeType,
                'effective_start' => $data['effective_start'] ?? null,
                'effective_end' => $data['effective_end'] ?? null,
                'status' => (string) ($data['status'] ?? 'enabled'),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['user_id' => (int) $data['user_id'], 'scope_type' => $scopeType, 'allowed_campus_ids' => $campusIds];
        });
    }

    /**
     * @return int[]
     */
    public function allowedCampusIds(EducationUserContext $context, bool $mobile = false): array
    {
        if ($mobile) {
            return array_values(array_map('intval', $context->campusIds));
        }
        $scopes = $this->repository->userScopes($context->userId, $context);
        foreach (['group_all', 'org_tree', 'campus_set'] as $type) {
            foreach ($scopes as $scope) {
                if ($scope['scope_type'] !== $type) {
                    continue;
                }
                $value = \is_array($scope['scope_value_json']) ? $scope['scope_value_json'] : [];
                $campusIds = array_values(array_map('intval', $value['campus_ids'] ?? []));
                if ($campusIds !== []) {
                    sort($campusIds);

                    return $campusIds;
                }
            }
        }

        return array_values(array_map('intval', $context->campusIds));
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    /**
     * @return array{allowed_campus_ids: int[]}
     */
    public function preview(EducationUserContext $context): array
    {
        return ['allowed_campus_ids' => $this->allowedCampusIds($context)];
    }
}

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
use App\Model\Education\Group\EducationOrgUnit;
use App\Repository\Education\Group\OrgUnitRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class OrgUnitService
{
    public function __construct(private readonly OrgUnitRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, path: string, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
            $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int) $data['parent_id'] : null;
            $parent = $parentId === null ? null : $this->repository->find($parentId, $context);
            if ($parentId !== null && ! $parent instanceof EducationOrgUnit) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'parent org unit not found', ['parent_id' => $parentId]);
            }
            if ($id !== null && $parent instanceof EducationOrgUnit && $this->createsCycle($id, $parent)) {
                throw new BusinessException(ResultCode::CONFLICT, 'org unit parent creates cycle', ['org_unit_id' => $id, 'parent_id' => $parentId]);
            }
            $payload = [
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'parent_id' => $parentId,
                'code' => (string) ($data['code'] ?? ''),
                'name' => (string) ($data['name'] ?? ''),
                'unit_type' => (string) ($data['unit_type'] ?? 'group'),
                'path' => '',
                'level' => $parent instanceof EducationOrgUnit ? ((int) $parent->level + 1) : 1,
                'status' => (string) ($data['status'] ?? 'enabled'),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ];
            if ($payload['code'] === '') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'code is required', ['field' => 'code']);
            }
            if ($id === null && $this->repository->findByCode((int) $context->tenantId, $payload['code']) instanceof EducationOrgUnit) {
                throw new BusinessException(ResultCode::CONFLICT, 'org unit code already exists', ['code' => $payload['code']]);
            }
            $row = $id === null ? $this->repository->create($payload) : $this->repository->update($this->mustFind($id, $context), $payload);
            $path = $parent instanceof EducationOrgUnit ? ((string) $parent->path . '/' . (int) $row->id) : (string) $row->id;
            $row = $this->repository->update($row, ['path' => $path, 'updated_by' => $context->userId]);

            return ['id' => (int) $row->id, 'path' => (string) $row->path, 'status' => (string) $row->status];
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function tree(EducationUserContext $context, array $filters = []): array
    {
        return $this->repository->tree($context, $filters);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{org_unit_id: int, campus_id: int}
     */
    public function bindCampus(array $data, EducationUserContext $context): array
    {
        $row = $this->repository->bindCampus([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => (int) $data['campus_id'],
            'org_unit_id' => (int) $data['org_unit_id'],
            'relation_type' => (string) ($data['relation_type'] ?? 'owned'),
            'effective_start' => $data['effective_start'] ?? null,
            'effective_end' => $data['effective_end'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['org_unit_id' => (int) $row->org_unit_id, 'campus_id' => (int) $row->campus_id];
    }

    private function mustFind(int $id, EducationUserContext $context): EducationOrgUnit
    {
        $row = $this->repository->find($id, $context);
        if (! $row instanceof EducationOrgUnit) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'org unit not found', ['org_unit_id' => $id]);
        }

        return $row;
    }

    private function createsCycle(int $id, EducationOrgUnit $parent): bool
    {
        return $id === (int) $parent->id || \in_array((string) $id, explode('/', (string) $parent->path), true);
    }
}

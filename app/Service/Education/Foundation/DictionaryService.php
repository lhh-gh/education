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
use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Enums\Education\Foundation\DictionaryStatus;
use App\Repository\Education\Foundation\DictItemRepository;
use App\Repository\Education\Foundation\DictTypeRepository;
use App\Repository\Education\Foundation\TenantRepository;
use Hyperf\Collection\Collection;

final class DictionaryService
{
    public function __construct(
        private readonly DictTypeRepository $typeRepository,
        private readonly DictItemRepository $itemRepository,
        private readonly TenantRepository $tenantRepository,
        private readonly ConfigOwnerResolver $ownerResolver
    ) {}

    public function pageTypes(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $params = $this->applyReadScope($params, $context);

        return $this->typeRepository->page($params, $page, $pageSize);
    }

    public function pageItems(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $params = $this->applyReadScope($params, $context);

        return $this->itemRepository->page($params, $page, $pageSize);
    }

    public function createType(array $data, EducationUserContext $context, ?int $operatorId): EducationDictType
    {
        $tenantId = $this->normalizeTenantId($data['tenant_id'] ?? null);
        $ownerType = (string) $data['owner_type'];
        $this->ownerResolver->assertCanWriteOwner($context, $ownerType, $tenantId);
        $this->assertTenantExists($ownerType, $tenantId);
        $ownerKey = $this->ownerResolver->resolveOwnerKey($ownerType, $tenantId);
        $code = (string) $data['code'];
        $this->assertUniqueTypeCode($ownerKey, $code);

        return $this->typeRepository->create([
            'owner_type' => $ownerType,
            'tenant_id' => $tenantId,
            'owner_key' => $ownerKey,
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $this->normalizeStatus((string) ($data['status'] ?? DictionaryStatus::Enabled->value)),
            'is_locked' => (bool) ($data['is_locked'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);
    }

    public function updateType(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationDictType
    {
        $type = $this->findTypeOrFail($id);
        $this->assertTypeWritable($type, $context);
        $type->fill([
            'name' => $data['name'] ?? $type->name,
            'description' => $data['description'] ?? $type->description,
            'status' => $this->normalizeStatus((string) ($data['status'] ?? $type->status)),
            'is_locked' => (bool) ($data['is_locked'] ?? $type->is_locked),
            'sort_order' => (int) ($data['sort_order'] ?? $type->sort_order),
            'updated_by' => $operatorId,
        ]);
        $type->save();

        return $type->refresh();
    }

    public function changeTypeStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationDictType
    {
        $type = $this->findTypeOrFail($id);
        $this->assertTypeWritable($type, $context);
        $type->fill([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ]);
        $type->save();

        return $type->refresh();
    }

    public function deleteType(int $id, EducationUserContext $context): void
    {
        $type = $this->findTypeOrFail($id);
        $this->assertTypeWritable($type, $context);
        if ($type->is_locked) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'locked system dictionary cannot be deleted');
        }
        if ($type->items()->exists()) {
            throw new BusinessException(ResultCode::FAIL, 'dictionary type has items');
        }

        $type->delete();
    }

    public function createItem(array $data, EducationUserContext $context, ?int $operatorId): EducationDictItem
    {
        $type = $this->findTypeOrFail((int) $data['dict_type_id']);
        $this->assertTypeWritable($type, $context);
        $value = (string) $data['value'];
        $this->assertUniqueItemValue((int) $type->id, $value);

        return $this->itemRepository->create([
            'dict_type_id' => $type->id,
            'owner_key' => $type->owner_key,
            'dict_code' => $type->code,
            'label' => $data['label'],
            'value' => $value,
            'color' => $data['color'] ?? null,
            'extra' => $data['extra'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => $this->normalizeStatus((string) ($data['status'] ?? DictionaryStatus::Enabled->value)),
            'is_default' => (bool) ($data['is_default'] ?? false),
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);
    }

    public function updateItem(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationDictItem
    {
        $item = $this->findItemOrFail($id);
        $type = $this->findTypeOrFail((int) $item->dict_type_id);
        $this->assertTypeWritable($type, $context);
        $value = (string) ($data['value'] ?? $item->value);
        $this->assertUniqueItemValue((int) $type->id, $value, $id);
        $item->fill([
            'label' => $data['label'] ?? $item->label,
            'value' => $value,
            'color' => $data['color'] ?? $item->color,
            'extra' => $data['extra'] ?? $item->extra,
            'sort_order' => (int) ($data['sort_order'] ?? $item->sort_order),
            'status' => $this->normalizeStatus((string) ($data['status'] ?? $item->status)),
            'is_default' => (bool) ($data['is_default'] ?? $item->is_default),
            'updated_by' => $operatorId,
        ]);
        $item->save();

        return $item->refresh();
    }

    public function changeItemStatus(int $id, string $status, EducationUserContext $context, ?int $operatorId): EducationDictItem
    {
        $item = $this->findItemOrFail($id);
        $type = $this->findTypeOrFail((int) $item->dict_type_id);
        $this->assertTypeWritable($type, $context);
        $item->fill([
            'status' => $this->normalizeStatus($status),
            'updated_by' => $operatorId,
        ]);
        $item->save();

        return $item->refresh();
    }

    public function deleteItem(int $id, EducationUserContext $context): void
    {
        $item = $this->findItemOrFail($id);
        $type = $this->findTypeOrFail((int) $item->dict_type_id);
        $this->assertTypeWritable($type, $context);
        $item->delete();
    }

    public function items(string $dictCode, ?int $tenantId): array
    {
        if ($tenantId !== null) {
            $tenantType = $this->typeRepository->findByOwnerCode($this->ownerResolver->tenantOwnerKey($tenantId), $dictCode);
            if ($tenantType instanceof EducationDictType) {
                return $this->itemsForType($tenantType)->toArray();
            }
        }

        $systemType = $this->typeRepository->findByOwnerCode($this->ownerResolver->systemOwnerKey(), $dictCode);
        if (! $systemType instanceof EducationDictType) {
            return [];
        }

        return $this->itemsForType($systemType)->toArray();
    }

    private function applyReadScope(array $params, EducationUserContext $context): array
    {
        if ($context->platformAccess) {
            return $params;
        }

        $ownerKeys = [$this->ownerResolver->systemOwnerKey()];
        if ($context->tenantId !== null) {
            $ownerKeys[] = $this->ownerResolver->tenantOwnerKey($context->tenantId);
        }
        if (isset($params['tenant_id']) && (int) $params['tenant_id'] !== (int) $context->tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => (int) $params['tenant_id']]);
        }

        $params['owner_keys'] = $ownerKeys;

        return $params;
    }

    /**
     * @return Collection<int, EducationDictItem>
     */
    private function itemsForType(EducationDictType $type): Collection
    {
        if ($type->status !== DictionaryStatus::Enabled->value) {
            return new Collection();
        }

        return $this->itemRepository->enabledItems($type->owner_key, $type->code);
    }

    private function findTypeOrFail(int $id): EducationDictType
    {
        $type = $this->typeRepository->findById($id);
        if (! $type instanceof EducationDictType) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $type;
    }

    private function findItemOrFail(int $id): EducationDictItem
    {
        $item = $this->itemRepository->findById($id);
        if (! $item instanceof EducationDictItem) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $item;
    }

    private function assertTypeWritable(EducationDictType $type, EducationUserContext $context): void
    {
        $this->ownerResolver->assertCanWriteOwner($context, $type->owner_type, $type->tenant_id);
    }

    private function assertTenantExists(string $ownerType, ?int $tenantId): void
    {
        if ($ownerType !== 'tenant') {
            return;
        }
        if ($tenantId === null || ! $this->tenantRepository->existsById($tenantId)) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }
    }

    private function assertUniqueTypeCode(string $ownerKey, string $code): void
    {
        if ($this->typeRepository->existsByOwnerCode($ownerKey, $code)) {
            throw new BusinessException(ResultCode::CONFLICT, 'dictionary code already exists', ['owner_key' => $ownerKey, 'code' => $code]);
        }
    }

    private function assertUniqueItemValue(int $dictTypeId, string $value, ?int $ignoreId = null): void
    {
        if ($this->itemRepository->existsValue($dictTypeId, $value, $ignoreId)) {
            throw new BusinessException(ResultCode::CONFLICT, 'dictionary item value already exists', ['dict_type_id' => $dictTypeId, 'value' => $value]);
        }
    }

    private function normalizeStatus(string $status): string
    {
        if (DictionaryStatus::tryFrom($status) === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'invalid dictionary status');
        }

        return DictionaryStatus::from($status)->value;
    }

    private function normalizeTenantId(mixed $tenantId): ?int
    {
        if ($tenantId === null || $tenantId === '') {
            return null;
        }

        return (int) $tenantId;
    }
}

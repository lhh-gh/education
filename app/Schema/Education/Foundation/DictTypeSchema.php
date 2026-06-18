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

use App\Model\Education\Foundation\EducationDictType;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationDictTypeSchema')]
final class DictTypeSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'owner_type', title: 'Owner type', type: 'string')]
    public ?string $ownerType;

    #[Property(property: 'tenant_id', title: 'Tenant ID', type: 'int', nullable: true)]
    public ?int $tenantId;

    #[Property(property: 'owner_key', title: 'Owner key', type: 'string')]
    public ?string $ownerKey;

    #[Property(property: 'code', title: 'Code', type: 'string')]
    public ?string $code;

    #[Property(property: 'name', title: 'Name', type: 'string')]
    public ?string $name;

    #[Property(property: 'description', title: 'Description', type: 'string', nullable: true)]
    public ?string $description;

    #[Property(property: 'status', title: 'Status', type: 'string')]
    public ?string $status;

    #[Property(property: 'is_locked', title: 'Locked', type: 'bool')]
    public ?bool $isLocked;

    #[Property(property: 'sort_order', title: 'Sort order', type: 'int')]
    public ?int $sortOrder;

    #[Property(property: 'item_count', title: 'Item count', type: 'int', nullable: true)]
    public ?int $itemCount;

    #[Property(property: 'created_by', title: 'Created by', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: 'Updated by', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: 'Updated at', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationDictType $model)
    {
        $this->id = $model->id;
        $this->ownerType = $model->owner_type;
        $this->tenantId = $model->tenant_id;
        $this->ownerKey = $model->owner_key;
        $this->code = $model->code;
        $this->name = $model->name;
        $this->description = $model->description;
        $this->status = $model->status;
        $this->isLocked = $model->is_locked;
        $this->sortOrder = $model->sort_order;
        $this->itemCount = $model->item_count ?? null;
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $this->formatDate($model->created_at);
        $this->updatedAt = $this->formatDate($model->updated_at);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->ownerType,
            'tenant_id' => $this->tenantId,
            'owner_key' => $this->ownerKey,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'is_locked' => $this->isLocked,
            'sort_order' => $this->sortOrder,
            'item_count' => $this->itemCount,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        }

        return $value === null ? null : (string) $value;
    }
}

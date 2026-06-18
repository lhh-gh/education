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

use App\Model\Education\Foundation\EducationDictItem;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationDictItemSchema')]
final class DictItemSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'dict_type_id', title: 'Dictionary type ID', type: 'int')]
    public ?int $dictTypeId;

    #[Property(property: 'owner_key', title: 'Owner key', type: 'string')]
    public ?string $ownerKey;

    #[Property(property: 'dict_code', title: 'Dictionary code', type: 'string')]
    public ?string $dictCode;

    #[Property(property: 'label', title: 'Label', type: 'string')]
    public ?string $label;

    #[Property(property: 'value', title: 'Value', type: 'string')]
    public ?string $value;

    #[Property(property: 'color', title: 'Color', type: 'string', nullable: true)]
    public ?string $color;

    #[Property(property: 'extra', title: 'Extra', type: 'array', nullable: true)]
    public ?array $extra;

    #[Property(property: 'sort_order', title: 'Sort order', type: 'int')]
    public ?int $sortOrder;

    #[Property(property: 'status', title: 'Status', type: 'string')]
    public ?string $status;

    #[Property(property: 'is_default', title: 'Default', type: 'bool')]
    public ?bool $isDefault;

    #[Property(property: 'created_by', title: 'Created by', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: 'Updated by', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: 'Updated at', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationDictItem $model)
    {
        $this->id = $model->id;
        $this->dictTypeId = $model->dict_type_id;
        $this->ownerKey = $model->owner_key;
        $this->dictCode = $model->dict_code;
        $this->label = $model->label;
        $this->value = $model->value;
        $this->color = $model->color;
        $this->extra = $model->extra;
        $this->sortOrder = $model->sort_order;
        $this->status = $model->status;
        $this->isDefault = $model->is_default;
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $this->formatDate($model->created_at);
        $this->updatedAt = $this->formatDate($model->updated_at);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'dict_type_id' => $this->dictTypeId,
            'owner_key' => $this->ownerKey,
            'dict_code' => $this->dictCode,
            'label' => $this->label,
            'value' => $this->value,
            'color' => $this->color,
            'extra' => $this->extra,
            'sort_order' => $this->sortOrder,
            'status' => $this->status,
            'is_default' => $this->isDefault,
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

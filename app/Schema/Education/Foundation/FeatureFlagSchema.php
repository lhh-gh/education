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

use App\Model\Education\Foundation\EducationFeatureFlag;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationFeatureFlagSchema')]
final class FeatureFlagSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'owner_type', title: 'Owner type', type: 'string')]
    public ?string $ownerType;

    #[Property(property: 'tenant_id', title: 'Tenant ID', type: 'int', nullable: true)]
    public ?int $tenantId;

    #[Property(property: 'owner_key', title: 'Owner key', type: 'string')]
    public ?string $ownerKey;

    #[Property(property: 'feature_code', title: 'Feature code', type: 'string')]
    public ?string $featureCode;

    #[Property(property: 'feature_name', title: 'Feature name', type: 'string')]
    public ?string $featureName;

    #[Property(property: 'description', title: 'Description', type: 'string', nullable: true)]
    public ?string $description;

    #[Property(property: 'enabled', title: 'Enabled', type: 'bool')]
    public ?bool $enabled;

    #[Property(property: 'config', title: 'Config', type: 'array', nullable: true)]
    public ?array $config;

    #[Property(property: 'effective_from', title: 'Effective from', type: 'string', nullable: true)]
    public ?string $effectiveFrom;

    #[Property(property: 'effective_to', title: 'Effective to', type: 'string', nullable: true)]
    public ?string $effectiveTo;

    #[Property(property: 'status', title: 'Status', type: 'string')]
    public ?string $status;

    #[Property(property: 'is_locked', title: 'Locked', type: 'bool')]
    public ?bool $isLocked;

    #[Property(property: 'created_by', title: 'Created by', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: 'Updated by', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: 'Updated at', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationFeatureFlag $model)
    {
        $this->id = $model->id;
        $this->ownerType = $model->owner_type;
        $this->tenantId = $model->tenant_id;
        $this->ownerKey = $model->owner_key;
        $this->featureCode = $model->feature_code;
        $this->featureName = $model->feature_name;
        $this->description = $model->description;
        $this->enabled = $model->enabled;
        $this->config = $model->config;
        $this->effectiveFrom = $this->formatDate($model->effective_from);
        $this->effectiveTo = $this->formatDate($model->effective_to);
        $this->status = $model->status;
        $this->isLocked = $model->is_locked;
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
            'feature_code' => $this->featureCode,
            'feature_name' => $this->featureName,
            'description' => $this->description,
            'enabled' => $this->enabled,
            'config' => $this->config,
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'status' => $this->status,
            'is_locked' => $this->isLocked,
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

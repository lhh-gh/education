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
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationTenantSchema')]
final class TenantSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'name', title: '租户名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'code', title: '租户编码', type: 'string')]
    public ?string $code;

    #[Property(property: 'short_name', title: '租户简称', type: 'string', nullable: true)]
    public ?string $shortName;

    #[Property(property: 'contact_name', title: '联系人', type: 'string', nullable: true)]
    public ?string $contactName;

    #[Property(property: 'contact_phone', title: '联系电话', type: 'string', nullable: true)]
    public ?string $contactPhone;

    #[Property(property: 'status', title: '状态', type: 'string')]
    public ?string $status;

    #[Property(property: 'settings', title: '设置', type: 'array', nullable: true)]
    public ?array $settings;

    #[Property(property: 'enabled_at', title: '启用时间', type: 'string', nullable: true)]
    public ?string $enabledAt;

    #[Property(property: 'disabled_at', title: '停用时间', type: 'string', nullable: true)]
    public ?string $disabledAt;

    #[Property(property: 'created_by', title: '创建者', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: '更新者', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: '创建时间', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationTenant $model)
    {
        $this->id = $model->id;
        $this->name = $model->name;
        $this->code = $model->code;
        $this->shortName = $model->short_name;
        $this->contactName = $model->contact_name;
        $this->contactPhone = $model->contact_phone;
        $this->status = $model->status->value ?? $model->status;
        $this->settings = $model->settings;
        $this->enabledAt = $this->formatDate($model->enabled_at);
        $this->disabledAt = $this->formatDate($model->disabled_at);
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $this->formatDate($model->created_at);
        $this->updatedAt = $this->formatDate($model->updated_at);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'short_name' => $this->shortName,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'status' => $this->status,
            'settings' => $this->settings,
            'enabled_at' => $this->enabledAt,
            'disabled_at' => $this->disabledAt,
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

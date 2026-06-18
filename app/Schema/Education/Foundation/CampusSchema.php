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

use App\Model\Education\Foundation\EducationCampus;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationCampusSchema')]
final class CampusSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'tenant_id', title: '租户ID', type: 'int')]
    public ?int $tenantId;

    #[Property(property: 'name', title: '校区名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'code', title: '校区编码', type: 'string')]
    public ?string $code;

    #[Property(property: 'contact_name', title: '联系人', type: 'string', nullable: true)]
    public ?string $contactName;

    #[Property(property: 'contact_phone', title: '联系电话', type: 'string', nullable: true)]
    public ?string $contactPhone;

    #[Property(property: 'address', title: '地址', type: 'string', nullable: true)]
    public ?string $address;

    #[Property(property: 'status', title: '状态', type: 'string')]
    public ?string $status;

    #[Property(property: 'settings', title: '设置', type: 'array', nullable: true)]
    public ?array $settings;

    #[Property(property: 'created_by', title: '创建者', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: '更新者', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: '创建时间', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationCampus $model)
    {
        $this->id = $model->id;
        $this->tenantId = $model->tenant_id;
        $this->name = $model->name;
        $this->code = $model->code;
        $this->contactName = $model->contact_name;
        $this->contactPhone = $model->contact_phone;
        $this->address = $model->address;
        $this->status = $model->status->value ?? $model->status;
        $this->settings = $model->settings;
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $this->formatDate($model->created_at);
        $this->updatedAt = $this->formatDate($model->updated_at);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'code' => $this->code,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'address' => $this->address,
            'status' => $this->status,
            'settings' => $this->settings,
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

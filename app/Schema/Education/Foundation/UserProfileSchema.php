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

use App\Model\Education\Foundation\EducationUserProfile;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationUserProfileSchema')]
final class UserProfileSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'profile_key', title: 'Profile key', type: 'string')]
    public ?string $profileKey;

    #[Property(property: 'tenant_id', title: 'Tenant ID', type: 'int', nullable: true)]
    public ?int $tenantId;

    #[Property(property: 'user_id', title: 'User ID', type: 'int')]
    public ?int $userId;

    #[Property(property: 'role_code', title: 'Role code', type: 'string')]
    public ?string $roleCode;

    #[Property(property: 'display_name', title: 'Display name', type: 'string')]
    public ?string $displayName;

    #[Property(property: 'mobile', title: 'Mobile', type: 'string', nullable: true)]
    public ?string $mobile;

    #[Property(property: 'avatar', title: 'Avatar', type: 'string', nullable: true)]
    public ?string $avatar;

    #[Property(property: 'openid', title: 'OpenID', type: 'string', nullable: true)]
    public ?string $openid;

    #[Property(property: 'unionid', title: 'UnionID', type: 'string', nullable: true)]
    public ?string $unionid;

    #[Property(property: 'status', title: 'Status', type: 'string')]
    public ?string $status;

    #[Property(property: 'current_campus_id', title: 'Current campus ID', type: 'int', nullable: true)]
    public ?int $currentCampusId;

    #[Property(property: 'settings', title: 'Settings', type: 'array', nullable: true)]
    public ?array $settings;

    #[Property(property: 'created_by', title: 'Created by', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: 'Updated by', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: 'Updated at', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationUserProfile $model)
    {
        $this->id = $model->id;
        $this->profileKey = $model->profile_key;
        $this->tenantId = $model->tenant_id;
        $this->userId = $model->user_id;
        $this->roleCode = $model->role_code->value ?? $model->role_code;
        $this->displayName = $model->display_name;
        $this->mobile = $model->mobile;
        $this->avatar = $model->avatar;
        $this->openid = $model->openid;
        $this->unionid = $model->unionid;
        $this->status = $model->status->value ?? $model->status;
        $this->currentCampusId = $model->current_campus_id;
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
            'profile_key' => $this->profileKey,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'role_code' => $this->roleCode,
            'display_name' => $this->displayName,
            'mobile' => $this->mobile,
            'avatar' => $this->avatar,
            'openid' => $this->openid,
            'unionid' => $this->unionid,
            'status' => $this->status,
            'current_campus_id' => $this->currentCampusId,
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

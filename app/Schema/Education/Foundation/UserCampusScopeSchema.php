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

use App\Model\Education\Foundation\EducationUserCampusScope;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationUserCampusScopeSchema')]
final class UserCampusScopeSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'tenant_id', title: 'Tenant ID', type: 'int')]
    public ?int $tenantId;

    #[Property(property: 'user_profile_id', title: 'User profile ID', type: 'int')]
    public ?int $userProfileId;

    #[Property(property: 'user_id', title: 'User ID', type: 'int')]
    public ?int $userId;

    #[Property(property: 'campus_id', title: 'Campus ID', type: 'int')]
    public ?int $campusId;

    #[Property(property: 'created_by', title: 'Created by', type: 'int', nullable: true)]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: 'Updated by', type: 'int', nullable: true)]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: 'Updated at', type: 'string', nullable: true)]
    public ?string $updatedAt;

    public function __construct(EducationUserCampusScope $model)
    {
        $this->id = $model->id;
        $this->tenantId = $model->tenant_id;
        $this->userProfileId = $model->user_profile_id;
        $this->userId = $model->user_id;
        $this->campusId = $model->campus_id;
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
            'user_profile_id' => $this->userProfileId,
            'user_id' => $this->userId,
            'campus_id' => $this->campusId,
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

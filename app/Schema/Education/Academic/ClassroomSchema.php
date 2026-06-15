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

namespace App\Schema\Education\Academic;

use App\Model\Education\Academic\EducationClassroom;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationClassroomSchema')]
final class ClassroomSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationClassroom $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'code' => $this->model->code,
            'name' => $this->model->name,
            'capacity' => $this->model->capacity,
            'location' => $this->model->location,
            'equipment' => $this->model->equipment,
            'status' => $this->model->status,
            'sort_order' => $this->model->sort_order,
            'remark' => $this->model->remark,
            'created_at' => $this->formatDate($this->model->created_at),
            'updated_at' => $this->formatDate($this->model->updated_at),
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT) : ($value === null ? null : (string) $value);
    }
}

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

use App\Model\Education\Academic\EducationLessonPackage;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationLessonPackageSchema')]
final class LessonPackageSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationLessonPackage $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'course_id' => $this->model->course_id,
            'course_name' => $this->model->course_name ?? null,
            'code' => $this->model->code,
            'name' => $this->model->name,
            'lesson_units' => $this->model->lesson_units,
            'bonus_units' => $this->model->bonus_units,
            'total_units' => $this->model->total_units,
            'list_price' => $this->model->list_price,
            'sale_price' => $this->model->sale_price,
            'validity_days' => $this->model->validity_days,
            'status' => $this->model->status,
            'sort_order' => $this->model->sort_order,
            'remark' => $this->model->remark,
            'created_at' => $this->formatDate($this->model->created_at),
            'updated_at' => $this->formatDate($this->model->updated_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}

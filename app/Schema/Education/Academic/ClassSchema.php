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

use App\Model\Education\Academic\EducationClass;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationClassSchema')]
final class ClassSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationClass $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'course_id' => $this->model->course_id,
            'main_teacher_id' => $this->model->main_teacher_id,
            'classroom_id' => $this->model->classroom_id,
            'code' => $this->model->code,
            'name' => $this->model->name,
            'class_type' => $this->model->class_type,
            'max_students' => $this->model->max_students,
            'start_date' => $this->formatDate($this->model->start_date, 'Y-m-d'),
            'end_date' => $this->formatDate($this->model->end_date, 'Y-m-d'),
            'lesson_units' => $this->model->lesson_units,
            'status' => $this->model->status,
            'schedule_note' => $this->model->schedule_note,
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

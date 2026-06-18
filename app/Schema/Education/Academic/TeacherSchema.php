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

use App\Model\Education\Academic\EducationTeacher;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationTeacherSchema')]
final class TeacherSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationTeacher $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'user_profile_id' => $this->model->user_profile_id,
            'teacher_no' => $this->model->teacher_no,
            'name' => $this->model->name,
            'mobile' => $this->model->mobile,
            'gender' => $this->model->gender,
            'birthday' => $this->formatDate($this->model->birthday, 'Y-m-d'),
            'title' => $this->model->title,
            'hire_date' => $this->formatDate($this->model->hire_date, 'Y-m-d'),
            'avatar' => $this->model->avatar,
            'introduction' => $this->model->introduction,
            'status' => $this->model->status,
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

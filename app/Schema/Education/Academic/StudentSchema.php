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

use App\Model\Education\Academic\EducationStudent;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationStudentSchema')]
final class StudentSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationStudent $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'student_no' => $this->model->student_no,
            'name' => $this->model->name,
            'gender' => $this->model->gender,
            'birthday' => $this->formatDate($this->model->birthday, 'Y-m-d'),
            'mobile' => $this->model->mobile,
            'school' => $this->model->school,
            'grade' => $this->model->grade,
            'source' => $this->model->source,
            'avatar' => $this->model->avatar,
            'enrolled_at' => $this->formatDate($this->model->enrolled_at, 'Y-m-d'),
            'status' => $this->model->status,
            'remark' => $this->model->remark,
            'guardian_count' => $this->model->guardian_count ?? null,
            'created_at' => $this->formatDate($this->model->created_at),
            'updated_at' => $this->formatDate($this->model->updated_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}

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

use App\Model\Education\Academic\EducationStudentGuardian;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationStudentGuardianSchema')]
final class StudentGuardianSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationStudentGuardian $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'student_id' => $this->model->student_id,
            'guardian_id' => $this->model->guardian_id,
            'guardian_name' => $this->model->guardian?->name,
            'guardian_mobile' => $this->model->guardian?->mobile,
            'relation' => $this->model->relation,
            'is_primary' => $this->model->is_primary,
            'can_receive_notice' => $this->model->can_receive_notice,
            'can_submit_leave' => $this->model->can_submit_leave,
            'remark' => $this->model->remark,
        ];
    }
}

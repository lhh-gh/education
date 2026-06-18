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

use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationTeacherCourseSchema')]
final class TeacherCourseSchema implements \JsonSerializable
{
    public function __construct(private readonly array $row) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->row['id'] ?? null,
            'tenant_id' => $this->row['tenant_id'] ?? null,
            'campus_id' => $this->row['campus_id'] ?? null,
            'course_id' => $this->row['course_id'] ?? null,
            'teacher_id' => $this->row['teacher_id'] ?? null,
            'teacher_name' => $this->row['teacher_name'] ?? null,
            'teacher_no' => $this->row['teacher_no'] ?? null,
            'teacher_mobile' => $this->row['teacher_mobile'] ?? null,
            'status' => $this->row['status'] ?? null,
            'authorized_at' => $this->row['authorized_at'] ?? null,
            'remark' => $this->row['remark'] ?? null,
        ];
    }
}

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

#[Schema(title: 'EducationMakeupLessonResultSchema')]
final class MakeupLessonResultSchema implements \JsonSerializable
{
    public function __construct(private readonly array $result) {}

    public function jsonSerialize(): mixed
    {
        return [
            'leave_request' => $this->result['leave_request']->toArray(),
            'target_lesson' => $this->result['target_lesson']->toArray(),
            'target_lesson_student' => $this->result['target_lesson_student']->toArray(),
            'change_record' => $this->result['change_record']->toArray(),
        ];
    }
}

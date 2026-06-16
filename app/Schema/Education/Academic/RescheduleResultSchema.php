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

#[Schema(title: 'EducationRescheduleResultSchema')]
final class RescheduleResultSchema implements \JsonSerializable
{
    public function __construct(private readonly array $result) {}

    public function jsonSerialize(): mixed
    {
        return [
            'lesson' => $this->result['lesson']->toArray(),
            'change_record' => $this->result['change_record']->toArray(),
        ];
    }
}

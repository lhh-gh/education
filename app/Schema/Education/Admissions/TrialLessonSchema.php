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

namespace App\Schema\Education\Admissions;

final class TrialLessonSchema implements \JsonSerializable
{
    public function __construct(private readonly array $data = []) {}

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

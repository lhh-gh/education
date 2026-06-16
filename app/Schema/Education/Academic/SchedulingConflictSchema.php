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

use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationSchedulingConflictSchema')]
final class SchedulingConflictSchema implements \JsonSerializable
{
    #[Property(property: 'has_conflict', title: 'Has conflict', type: 'bool')]
    public ?bool $hasConflict = null;

    public function __construct(private readonly array $row = []) {}

    public function jsonSerialize(): mixed
    {
        return $this->row;
    }
}

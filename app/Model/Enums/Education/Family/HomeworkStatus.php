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

namespace App\Model\Enums\Education\Family;

enum HomeworkStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Assigned = 'assigned';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Overdue = 'overdue';
    case Withdrawn = 'withdrawn';
}

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

namespace App\Model\Enums\Education\Admissions;

enum TrialLessonStatus: string
{
    case Scheduled = 'scheduled';
    case Attended = 'attended';
    case Absent = 'absent';
    case Cancelled = 'cancelled';
    case Converted = 'converted';
}

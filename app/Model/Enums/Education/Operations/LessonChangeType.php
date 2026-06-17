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

namespace App\Model\Enums\Education\Operations;

enum LessonChangeType: string
{
    case Reschedule = 'reschedule';
    case Suspend = 'suspend';
    case Cancel = 'cancel';
    case ReplaceTeacher = 'replace_teacher';
    case ReplaceClassroom = 'replace_classroom';
    case SubstituteTeacher = 'substitute_teacher';
}

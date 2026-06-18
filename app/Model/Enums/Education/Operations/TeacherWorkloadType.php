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

enum TeacherWorkloadType: string
{
    case Main = 'main';
    case Substitute = 'substitute';
    case Makeup = 'makeup';
    case TrialSupport = 'trial_support';
}

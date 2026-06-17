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

namespace App\Repository\Education\Admissions;

use App\Model\Education\Admissions\EducationTrialFeedback;

final class TrialFeedbackRepository
{
    public function create(array $data): EducationTrialFeedback
    {
        return EducationTrialFeedback::query()->create($data);
    }
}

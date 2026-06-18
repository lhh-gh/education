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

namespace App\Repository\Education\Standards;

use App\Model\Education\Standards\EducationCourseFeedbackRecord;

final class CourseFeedbackRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationCourseFeedbackRecord
    {
        return EducationCourseFeedbackRecord::query()->create($data);
    }
}

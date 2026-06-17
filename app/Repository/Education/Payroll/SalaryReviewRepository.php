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

namespace App\Repository\Education\Payroll;

use App\Model\Education\Payroll\EducationTeacherSalaryAdjustment;
use App\Model\Education\Payroll\EducationTeacherSalaryReview;

final class SalaryReviewRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function createReview(array $data): EducationTeacherSalaryReview
    {
        return EducationTeacherSalaryReview::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAdjustment(array $data): EducationTeacherSalaryAdjustment
    {
        return EducationTeacherSalaryAdjustment::query()->create($data);
    }
}

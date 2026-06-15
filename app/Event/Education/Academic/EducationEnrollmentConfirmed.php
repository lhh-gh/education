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

namespace App\Event\Education\Academic;

use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Foundation\EducationUserContext;

final class EducationEnrollmentConfirmed
{
    public function __construct(
        public readonly EducationEnrollment $enrollment,
        public readonly EducationStudentCourseAccount $account,
        public readonly EducationUserContext $context
    ) {}
}

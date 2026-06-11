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

namespace App\Model\Enums\Education\Foundation;

enum EducationRoleCode: string
{
    case PlatformSuperAdmin = 'platform_super_admin';
    case PlatformOperator = 'platform_operator';
    case TenantAdmin = 'tenant_admin';
    case Principal = 'principal';
    case AcademicStaff = 'academic_staff';
    case FrontDesk = 'front_desk';
    case Teacher = 'teacher';
    case Finance = 'finance';
    case Guardian = 'guardian';

    public function isPlatform(): bool
    {
        return in_array($this, [self::PlatformSuperAdmin, self::PlatformOperator], true);
    }

    public function requiresCampusScope(): bool
    {
        return in_array($this, [self::Principal, self::AcademicStaff, self::FrontDesk, self::Teacher, self::Finance], true);
    }
}

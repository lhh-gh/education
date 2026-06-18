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

namespace App\Contract\Education\Foundation;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Model\Education\Foundation\EducationAuditLog;

interface AuditLoggerInterface
{
    public function record(EducationAuditEvent $event): EducationAuditLog;
}

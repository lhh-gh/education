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

namespace App\Listener\Education\Foundation;

use App\Contract\Education\Foundation\AuditLoggerInterface;
use App\Event\Education\Foundation\EducationAuditEvent;
use Hyperf\Event\Contract\ListenerInterface;

final class EducationAuditListener implements ListenerInterface
{
    public function __construct(
        private readonly AuditLoggerInterface $logger
    ) {}

    public function listen(): array
    {
        return [
            EducationAuditEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $event instanceof EducationAuditEvent) {
            return;
        }

        $this->logger->record($event);
    }
}

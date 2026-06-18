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

namespace App\Event\Education\Foundation;

use App\Model\Enums\Education\Foundation\AuditActorType;
use App\Service\Education\Foundation\EducationUserContext;

final class EducationAuditEvent
{
    public function __construct(
        public readonly string $module,
        public readonly string $resource,
        public readonly string $action,
        public readonly string $businessType,
        public readonly int|string|null $businessId,
        public readonly ?EducationUserContext $context,
        public readonly array $beforeSnapshot = [],
        public readonly array $afterSnapshot = [],
        public readonly array $metadata = [],
        public readonly ?string $summary = null,
        public readonly string $actorType = 'admin',
    ) {
        foreach ([
            'module' => $module,
            'resource' => $resource,
            'action' => $action,
            'businessType' => $businessType,
        ] as $name => $value) {
            if (trim($value) === '') {
                throw new \InvalidArgumentException($name . ' must not be empty');
            }
        }

        if (AuditActorType::tryFrom($actorType) === null) {
            throw new \InvalidArgumentException('actorType must be one of admin, teacher, guardian, system');
        }

        if ($context === null && $actorType !== AuditActorType::System->value) {
            throw new \InvalidArgumentException('context can be null only for system audit events');
        }
    }
}

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

namespace App\Http\Admin\Controller\Education\Workflow;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Common\ResultCode;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Psr\EventDispatcher\EventDispatcherInterface;

trait WorkflowControllerTrait
{
    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }

    private function tenantId(EducationUserContext $context): int
    {
        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant education profile is required');
        }

        return (int) $context->tenantId;
    }

    /**
     * @param array<string, mixed> $after
     */
    private function audit(EventDispatcherInterface $events, string $action, string $businessType, int|string|null $businessId, EducationUserContext $context, array $after = []): void
    {
        $events->dispatch(new EducationAuditEvent(
            module: 'workflow',
            resource: $businessType,
            action: $action,
            businessType: $businessType,
            businessId: $businessId,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: $after,
            metadata: ['tenant_id' => $context->tenantId, 'campus_id' => $context->currentCampusId],
            summary: $action
        ));
    }
}

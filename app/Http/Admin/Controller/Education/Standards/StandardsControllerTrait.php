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

namespace App\Http\Admin\Controller\Education\Standards;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Common\ResultCode;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

trait StandardsControllerTrait
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

    private function campusId(EducationUserContext $context): int
    {
        if ($context->currentCampusId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus context is required');
        }

        return (int) $context->currentCampusId;
    }

    private function pageNumber(RequestInterface $request): int
    {
        return max(1, (int) $request->input('page', 1));
    }

    private function pageSize(RequestInterface $request): int
    {
        return min(100, max(1, (int) $request->input('pageSize', 20)));
    }

    /**
     * @param array<string, mixed> $after
     */
    private function audit(EventDispatcherInterface $events, string $action, string $businessType, int|string|null $businessId, EducationUserContext $context, array $after = []): void
    {
        $events->dispatch(new EducationAuditEvent(
            module: 'standards',
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

    private function businessFailure(\RuntimeException $exception): BusinessException
    {
        $code = ResultCode::tryFrom($exception->getCode()) ?? ResultCode::FAIL;

        return new BusinessException($code, $exception->getMessage());
    }
}

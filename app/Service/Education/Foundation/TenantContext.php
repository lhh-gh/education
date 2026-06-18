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

namespace App\Service\Education\Foundation;

use App\Contract\Education\Foundation\TenantContextInterface;
use App\Exception\BusinessException;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;

final class TenantContext implements TenantContextInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly CurrentUser $currentUser,
        private readonly UserProfileService $profileService
    ) {}

    public function id(): int
    {
        $requestedTenantId = $this->requestedTenantId();
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            $context = $this->profileService->resolveForUser($this->currentUser->id(), $requestedTenantId);
            Context::set(ResolveEducationContextMiddleware::CONTEXT_KEY, $context);
        }

        if ($context->platformAccess) {
            if ($requestedTenantId === null) {
                throw new BusinessException(
                    ResultCode::UNPROCESSABLE_ENTITY,
                    'X-Tenant-Id header is required',
                    ['header' => 'X-Tenant-Id']
                );
            }
            $this->assertTenantExists($requestedTenantId);

            return $requestedTenantId;
        }

        if ($requestedTenantId !== null && $requestedTenantId !== $context->tenantId) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'tenant is outside current user scope',
                ['tenant_id' => $requestedTenantId]
            );
        }

        if ($context->tenantId === null) {
            throw new BusinessException(
                ResultCode::UNPROCESSABLE_ENTITY,
                'X-Tenant-Id header is required',
                ['header' => 'X-Tenant-Id']
            );
        }

        return $context->tenantId;
    }

    private function requestedTenantId(): ?int
    {
        $tenantId = (int) $this->request->header('X-Tenant-Id');

        return $tenantId > 0 ? $tenantId : null;
    }

    private function assertTenantExists(int $tenantId): void
    {
        if (! EducationTenant::query()->whereKey($tenantId)->exists()) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'education tenant not found', ['tenant_id' => $tenantId]);
        }
    }
}

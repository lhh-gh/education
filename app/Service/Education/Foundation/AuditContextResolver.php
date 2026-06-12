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

use Hyperf\Context\Context;
use Psr\Http\Message\ServerRequestInterface;

final class AuditContextResolver
{
    public function resolveRequestId(): ?string
    {
        $request = $this->request();
        if ($request === null) {
            return null;
        }

        $requestId = trim($request->getHeaderLine('X-Request-Id'));
        if ($requestId !== '') {
            return $requestId;
        }

        $attribute = $request->getAttribute('request_id');
        if (\is_scalar($attribute) && trim((string) $attribute) !== '') {
            return trim((string) $attribute);
        }

        return null;
    }

    public function resolveIpAddress(): ?string
    {
        $request = $this->request();
        if ($request === null) {
            return null;
        }

        $forwardedFor = trim($request->getHeaderLine('X-Forwarded-For'));
        if ($forwardedFor !== '') {
            $first = trim(explode(',', $forwardedFor)[0]);

            return $first === '' ? null : $first;
        }

        $serverParams = $request->getServerParams();
        foreach (['remote_addr', 'REMOTE_ADDR'] as $key) {
            if (! empty($serverParams[$key]) && \is_scalar($serverParams[$key])) {
                return (string) $serverParams[$key];
            }
        }

        return null;
    }

    public function resolveUserAgent(): ?string
    {
        $request = $this->request();
        if ($request === null) {
            return null;
        }

        $userAgent = trim($request->getHeaderLine('User-Agent'));

        return $userAgent === '' ? null : $userAgent;
    }

    public function resolveMethod(): ?string
    {
        return $this->request()?->getMethod();
    }

    public function resolvePath(): ?string
    {
        return $this->request()?->getUri()->getPath();
    }

    public function resolveActorRoleCode(?EducationUserContext $context): ?string
    {
        return $context?->roleCode->value;
    }

    public function resolveTenantId(?EducationUserContext $context): ?int
    {
        return $context?->tenantId;
    }

    public function resolveCampusId(?EducationUserContext $context, array $metadata): ?int
    {
        if ($context === null || ! isset($metadata['campus_id']) || $metadata['campus_id'] === '') {
            return null;
        }

        $campusId = (int) $metadata['campus_id'];
        if ($campusId <= 0) {
            return null;
        }

        if ($context->platformAccess || ! $context->roleCode->requiresCampusScope() || $context->canAccessCampus($campusId)) {
            return $campusId;
        }

        return null;
    }

    private function request(): ?ServerRequestInterface
    {
        $request = Context::get(ServerRequestInterface::class);

        return $request instanceof ServerRequestInterface ? $request : null;
    }
}

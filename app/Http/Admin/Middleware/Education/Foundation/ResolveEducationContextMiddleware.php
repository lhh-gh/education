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

namespace App\Http\Admin\Middleware\Education\Foundation;

use App\Http\CurrentUser;
use App\Service\Education\Foundation\UserProfileService;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResolveEducationContextMiddleware implements MiddlewareInterface
{
    public const CONTEXT_KEY = 'education.user_context';

    public function __construct(
        private readonly CurrentUser $currentUser,
        private readonly UserProfileService $profileService
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tenantId = $this->idFromRequest($request, 'X-Tenant-Id', 'tenant_id');
        $campusId = $this->idFromRequest($request, 'X-Campus-Id', 'campus_id');
        $context = $this->profileService->resolveForUser($this->currentUser->id(), $tenantId, $campusId);
        Context::set(self::CONTEXT_KEY, $context);

        return $handler->handle($request);
    }

    private function idFromRequest(ServerRequestInterface $request, string $header, string $field): ?int
    {
        $headerValue = $request->getHeaderLine($header);
        if ($headerValue !== '') {
            return $this->positiveId($headerValue);
        }

        $queryParams = $request->getQueryParams();
        if (isset($queryParams[$field])) {
            return $this->positiveId($queryParams[$field]);
        }

        $body = $request->getParsedBody();
        if (\is_array($body) && isset($body[$field])) {
            return $this->positiveId($body[$field]);
        }

        return null;
    }

    private function positiveId(mixed $value): ?int
    {
        if (\is_array($value) || \is_object($value)) {
            return null;
        }

        $id = (int) trim((string) $value);

        return $id > 0 ? $id : null;
    }
}

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
        $tenantId = $this->tenantIdFromHeader($request->getHeaderLine('X-Tenant-Id'));
        $context = $this->profileService->resolveForUser($this->currentUser->id(), $tenantId);
        Context::set(self::CONTEXT_KEY, $context);

        return $handler->handle($request);
    }

    private function tenantIdFromHeader(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        $tenantId = (int) $value;

        return $tenantId > 0 ? $tenantId : null;
    }
}

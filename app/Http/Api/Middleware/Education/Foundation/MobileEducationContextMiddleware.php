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

namespace App\Http\Api\Middleware\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Repository\Education\Foundation\MobileContextRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Lcobucci\JWT\Token\RegisteredClaims;
use Mine\Jwt\Factory;
use Mine\JwtAuth\Interfaces\CheckTokenInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MobileEducationContextMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CurrentUser $currentUser,
        private readonly MobileContextRepository $repository,
        private readonly Factory $jwtFactory,
        private readonly CheckTokenInterface $checkToken
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! str_starts_with($request->getUri()->getPath(), '/mobile/education/')) {
            return $handler->handle($request);
        }

        $userId = $this->currentUserId($request);
        $tenantId = $this->tenantIdFromHeader($request->getHeaderLine('X-Tenant-Id'));
        $profile = $this->repository->findAnyProfileForUser($userId, $tenantId);
        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education profile is required', ['user_id' => $userId]);
        }

        if ($profile->status !== UserProfileStatus::Enabled) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education profile is disabled', ['status' => 'disabled']);
        }

        $tenantId = $profile->tenant_id === null ? $tenantId : (int) $profile->tenant_id;
        $campusScopes = $tenantId === null ? [] : $this->repository->listCampusScopes($tenantId, $userId);
        $roleCode = $profile->role_code instanceof EducationRoleCode
            ? $profile->role_code
            : EducationRoleCode::from((string) $profile->role_code);

        Context::set(MobileContextService::CONTEXT_KEY, new EducationUserContext(
            userId: $userId,
            tenantId: $tenantId,
            roleCode: $roleCode,
            platformAccess: $roleCode->isPlatform(),
            campusIds: array_map(static fn (array $scope): int => (int) $scope['campus_id'], $campusScopes),
            currentCampusId: $profile->current_campus_id
        ));

        return $handler->handle($request);
    }

    private function currentUserId(ServerRequestInterface $request): int
    {
        try {
            return $this->currentUser->id();
        } catch (\Throwable) {
        }

        try {
            $token = $this->jwtFactory->get()->parserAccessToken($this->tokenFromRequest($request));
            $this->checkToken->checkJwt($token);
            return (int) $token->claims()->get(RegisteredClaims::ID);
        } catch (\Throwable) {
            throw new BusinessException(ResultCode::UNAUTHORIZED, 'mobile authentication required', ['required' => 'Authorization']);
        }
    }

    private function tokenFromRequest(ServerRequestInterface $request): string
    {
        if ($request->hasHeader('Authorization')) {
            return Str::replace('Bearer ', '', $request->getHeaderLine('Authorization'));
        }
        if ($request->hasHeader('token')) {
            return $request->getHeaderLine('token');
        }
        if (Arr::has($request->getQueryParams(), 'token')) {
            return (string) $request->getQueryParams()['token'];
        }

        return '';
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

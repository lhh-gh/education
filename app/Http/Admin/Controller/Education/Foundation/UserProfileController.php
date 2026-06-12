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

namespace App\Http\Admin\Controller\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\CampusScopeSaveRequest;
use App\Http\Admin\Request\Education\Foundation\UserProfilePageRequest;
use App\Http\Admin\Request\Education\Foundation\UserProfileSaveRequest;
use App\Http\Admin\Request\Education\Foundation\UserProfileStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Schema\Education\Foundation\UserProfileSchema;
use App\Service\Education\Foundation\CampusScopeService;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\UserProfileService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class UserProfileController extends AbstractController
{
    public function __construct(
        private readonly UserProfileService $service,
        private readonly CampusScopeService $campusScopeService,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/education/foundation/user-profiles/page',
        operationId: 'educationUserProfilePage',
        summary: 'User profile page',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[PageResponse(instance: UserProfileSchema::class)]
    #[Permission(code: 'education:foundation:user-profile:page')]
    public function page(UserProfilePageRequest $request): Result
    {
        return $this->success(
            $this->service->page(
                $request->validated(),
                $this->getCurrentPage(),
                $this->getPageSize(),
                $this->context()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/user-profiles',
        operationId: 'educationUserProfileCreate',
        summary: 'Create user profile',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: UserProfileSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:user-profile:create')]
    public function create(UserProfileSaveRequest $request): Result
    {
        $profile = $this->service->createProfile(
            $this->dataInsideContext($request->validated(), $this->context()),
            $this->currentUser->id()
        );

        return $this->success(['id' => $profile->id, 'profile_key' => $profile->profile_key]);
    }

    #[Put(
        path: '/admin/education/foundation/user-profiles/{id}',
        operationId: 'educationUserProfileUpdate',
        summary: 'Update user profile',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: UserProfileSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:user-profile:update')]
    public function update(int $id, UserProfileSaveRequest $request): Result
    {
        $context = $this->context();
        $this->assertProfileInsideContext($this->service->findProfileOrFail($id), $context);
        $profile = $this->service->updateProfile(
            $id,
            $this->dataInsideContext($request->validated(), $context),
            $this->currentUser->id()
        );

        return $this->success(['id' => $profile->id]);
    }

    #[Put(
        path: '/admin/education/foundation/user-profiles/{id}/status',
        operationId: 'educationUserProfileStatus',
        summary: 'User profile status',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: UserProfileStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:user-profile:status')]
    public function status(int $id, UserProfileStatusRequest $request): Result
    {
        $this->assertProfileInsideContext($this->service->findProfileOrFail($id), $this->context());
        $profile = $this->service->changeStatus($id, $request->validated()['status'], $this->currentUser->id());

        return $this->success(['id' => $profile->id, 'status' => $profile->status->value ?? $profile->status]);
    }

    #[Get(
        path: '/admin/education/foundation/user-profiles/{id}/campus-scopes',
        operationId: 'educationUserProfileCampusScopes',
        summary: 'Campus scopes',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus-scope:page')]
    public function campusScopes(int $id): Result
    {
        $profile = $this->service->findProfileOrFail($id);
        $tenantId = $this->scopedProfileTenantId($profile, $this->context());

        return $this->success([
            'user_profile_id' => $profile->id,
            'user_id' => $profile->user_id,
            'tenant_id' => $tenantId,
            'campus_ids' => $this->campusScopeService->campusIdsForProfile($tenantId, $id),
        ]);
    }

    #[Put(
        path: '/admin/education/foundation/user-profiles/{id}/campus-scopes',
        operationId: 'educationUserProfileSaveCampusScopes',
        summary: 'Save campus scopes',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: CampusScopeSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:campus-scope:save')]
    public function saveCampusScopes(int $id, CampusScopeSaveRequest $request): Result
    {
        $profile = $this->service->findProfileOrFail($id);
        $campusIds = $this->campusScopeService->saveScopes(
            profileId: $id,
            tenantId: $this->scopedProfileTenantId($profile, $this->context()),
            campusIds: $request->validated()['campus_ids'],
            operatorId: $this->currentUser->id()
        );

        return $this->success(['user_profile_id' => $profile->id, 'campus_ids' => $campusIds]);
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }

    private function dataInsideContext(array $data, EducationUserContext $context): array
    {
        if ($context->platformAccess) {
            return $data;
        }

        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope');
        }

        $tenantId = \array_key_exists('tenant_id', $data) && $data['tenant_id'] !== null && $data['tenant_id'] !== ''
            ? (int) $data['tenant_id']
            : $context->tenantId;
        if ($tenantId !== $context->tenantId) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current user scope', ['tenant_id' => $tenantId]);
        }

        $data['tenant_id'] = $context->tenantId;

        return $data;
    }

    private function scopedProfileTenantId(EducationUserProfile $profile, EducationUserContext $context): int
    {
        $this->assertProfileInsideContext($profile, $context);
        if ($profile->tenant_id === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'campus scope requires tenant profile');
        }

        return (int) $profile->tenant_id;
    }

    private function assertProfileInsideContext(EducationUserProfile $profile, EducationUserContext $context): void
    {
        if ($context->platformAccess) {
            return;
        }

        if ($context->tenantId === null || (int) $profile->tenant_id !== $context->tenantId) {
            throw new BusinessException(
                ResultCode::FORBIDDEN,
                'tenant is outside current user scope',
                ['tenant_id' => $profile->tenant_id]
            );
        }
    }
}

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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Foundation\MobileContextRepository;
use App\Schema\Education\Foundation\MobileContextSchema;
use App\Service\IService;
use Hyperf\Context\Context;

/**
 * @extends IService<EducationUserProfile>
 */
final class MobileContextService extends IService
{
    public const CONTEXT_KEY = 'education.mobile_context';

    public function __construct(
        protected readonly MobileContextRepository $repository,
        private readonly MobileContextSchema $schema
    ) {}

    public function mobile(): EducationUserContext
    {
        $context = Context::get(self::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::UNAUTHORIZED, 'mobile authentication required', ['required' => 'Authorization']);
        }

        return $context;
    }

    public function teacherContext(EducationUserContext $context, array $params): array
    {
        $this->assertRole($context, [EducationRoleCode::Teacher], 'teacher profile is required');
        $data = $this->baseContext($context, $params, '/pages/teacher/index', [
            ['key' => 'overview', 'label' => 'Overview', 'path' => '/pages/teacher/index'],
            ['key' => 'messages', 'label' => 'Messages', 'path' => '/pages/teacher/index'],
            ['key' => 'profile', 'label' => 'Profile', 'path' => '/pages/teacher/index'],
        ]);

        if ($data['campus_scopes'] === []) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher campus scope is required');
        }
        $this->assertCampusAllowed($data['campus_scopes'], $params['campus_id'] ?? null);

        return $data;
    }

    public function guardianContext(EducationUserContext $context, array $params): array
    {
        $this->assertRole($context, [EducationRoleCode::Guardian], 'guardian profile is not bound');
        $data = $this->baseContext($context, $params, '/pages/guardian/index', [
            ['key' => 'home', 'label' => 'Home', 'path' => '/pages/guardian/index'],
            ['key' => 'profile', 'label' => 'Profile', 'path' => '/pages/guardian/index'],
        ], [
            'code' => 'guardian_students_pending_v1',
            'message' => 'Student binding will be available in V1',
        ]);
        $data['bound_students'] = [];

        return $data;
    }

    public function operatorContext(EducationUserContext $context, array $params): array
    {
        $this->assertRole($context, [
            EducationRoleCode::TenantAdmin,
            EducationRoleCode::Principal,
            EducationRoleCode::AcademicStaff,
            EducationRoleCode::FrontDesk,
            EducationRoleCode::Finance,
        ], 'operator role is required');
        $data = $this->baseContext($context, $params, '/pages/operator/index', [
            ['key' => 'overview', 'label' => 'Overview', 'path' => '/pages/operator/index'],
            ['key' => 'profile', 'label' => 'Profile', 'path' => '/pages/operator/index'],
        ]);

        if ($context->roleCode !== EducationRoleCode::TenantAdmin && $data['campus_scopes'] === []) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'operator campus scope is required');
        }
        $this->assertCampusAllowed($data['campus_scopes'], $params['campus_id'] ?? null);

        return $data;
    }

    /**
     * @param EducationRoleCode[] $allowedRoles
     */
    private function assertRole(EducationUserContext $context, array $allowedRoles, string $message): void
    {
        if (! \in_array($context->roleCode, $allowedRoles, true)) {
            throw new BusinessException(ResultCode::FORBIDDEN, $message, ['role_code' => $context->roleCode->value]);
        }
    }

    /**
     * @param array<int, array{campus_id:int, campus_name:string}> $campusScopes
     */
    private function assertCampusAllowed(array $campusScopes, mixed $campusId): void
    {
        if ($campusId === null || $campusId === '') {
            return;
        }

        $campusId = (int) $campusId;
        foreach ($campusScopes as $campus) {
            if ((int) $campus['campus_id'] === $campusId) {
                return;
            }
        }

        throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current user scope', ['campus_id' => $campusId]);
    }

    private function baseContext(
        EducationUserContext $context,
        array $params,
        string $defaultPath,
        array $tabs,
        ?array $emptyState = null
    ): array {
        $tenantId = $this->requireTenantId($context);
        $profile = $this->repository->findProfileForUser($context->userId, $tenantId);
        if (! $profile instanceof EducationUserProfile) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education profile is required', ['user_id' => $context->userId]);
        }

        $tenant = $this->repository->findTenant($tenantId);
        if (! $tenant instanceof EducationTenant) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education tenant is disabled or missing', ['tenant_id' => $tenantId]);
        }

        $campusScopes = $context->roleCode === EducationRoleCode::TenantAdmin
            ? $this->repository->listEnabledCampuses($tenantId)
            : $this->repository->listCampusScopes($tenantId, $context->userId);

        return $this->schema->context(
            tenant: $tenant,
            profile: $profile,
            campusScopes: $campusScopes,
            featureFlags: $this->repository->listEnabledFeatureFlags($tenantId),
            defaultPath: $defaultPath,
            tabs: $tabs,
            emptyState: $emptyState,
            selectedCampusId: isset($params['campus_id']) ? (int) $params['campus_id'] : null
        );
    }

    private function requireTenantId(EducationUserContext $context): int
    {
        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant education profile is required');
        }

        return (int) $context->tenantId;
    }
}

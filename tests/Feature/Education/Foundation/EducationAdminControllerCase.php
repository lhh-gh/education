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

namespace HyperfTests\Feature\Education\Foundation;

use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use HyperfTests\Feature\Admin\ControllerCase;
use Psr\SimpleCache\CacheInterface;

abstract class EducationAdminControllerCase extends ControllerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy('current_user');
        Context::destroy('education.user_context');
        $this->clearCurrentUserCache();
        $this->cleanEducationData();
    }

    protected function tearDown(): void
    {
        $this->cleanEducationData();
        Context::destroy('current_user');
        Context::destroy('education.user_context');
        $this->clearCurrentUserCache();
        parent::tearDown();
    }

    protected function cleanEducationData(): void
    {
        EducationAuditLog::query()->whereRaw('1=1')->delete();
        EducationDictItem::query()->whereRaw('1=1')->forceDelete();
        EducationDictType::query()->whereRaw('1=1')->forceDelete();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
        EducationUserCampusScope::query()->delete();
        EducationUserProfile::query()->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    protected function authHeaders(array $headers = []): array
    {
        return array_merge(['Authorization' => 'Bearer ' . $this->token], $headers);
    }

    protected function grantPermissions(string ...$codes): void
    {
        foreach ($codes as $code) {
            $this->forAddPermission($code);
        }
    }

    protected function clearCurrentUserCache(): void
    {
        ApplicationContext::getContainer()
            ->get(CacheInterface::class)
            ->delete((string) $this->user->id);
    }

    protected function createEducationProfile(?int $tenantId = null, string $roleCode = 'platform_operator', string $status = 'enabled'): EducationUserProfile
    {
        return EducationUserProfile::query()->create([
            'profile_key' => $tenantId === null ? 'platform:' . $this->user->id : 'tenant:' . $tenantId . ':' . $this->user->id,
            'tenant_id' => $tenantId,
            'user_id' => $this->user->id,
            'role_code' => $roleCode,
            'display_name' => 'Education User',
            'status' => $status,
        ]);
    }
}

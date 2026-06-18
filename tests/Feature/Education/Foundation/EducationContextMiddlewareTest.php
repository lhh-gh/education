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

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationTenant;
use App\Service\Education\Foundation\UserProfileService;
use Hyperf\Context\ApplicationContext;

/**
 * @internal
 * @coversNothing
 */
final class EducationContextMiddlewareTest extends EducationAdminControllerCase
{
    public function testTenantProfileResolvesContextWithoutHeader(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'teacher');

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $context = ApplicationContext::getContainer()
            ->get(UserProfileService::class)
            ->resolveForUser((int) $this->user->id, null);
        self::assertSame($tenant->id, $context->tenantId);
        self::assertSame('teacher', $context->roleCode->value);
    }

    public function testTenantProfileCannotRequestOtherTenant(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'teacher');

        $result = $this->get('/admin/education/foundation/campuses/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantB->id]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testDisabledProfileIsRejected(): void
    {
        $this->grantPermissions('education:foundation:campus:page');
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'teacher', 'disabled');

        $result = $this->get('/admin/education/foundation/campuses/page', ['token' => $this->token]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}

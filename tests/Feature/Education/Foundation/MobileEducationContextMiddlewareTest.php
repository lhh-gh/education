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
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

/**
 * @internal
 * @coversNothing
 */
final class MobileEducationContextMiddlewareTest extends EducationAdminControllerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy(MobileContextService::CONTEXT_KEY);
    }

    protected function tearDown(): void
    {
        Context::destroy(MobileContextService::CONTEXT_KEY);
        parent::tearDown();
    }

    public function testUnauthenticatedMobileRequestReturns401(): void
    {
        $result = $this->get('/mobile/education/foundation/teacher/context');

        self::assertSame(ResultCode::UNAUTHORIZED->value, $result['code']);
        self::assertSame('mobile authentication required', $result['message']);
        self::assertSame('Authorization', $result['data']['required']);
    }

    public function testDisabledProfileReturns403(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'mobile_middleware', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'teacher', 'disabled');

        $result = $this->get(
            '/mobile/education/foundation/teacher/context',
            [],
            $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id])
        );

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
        self::assertSame('education profile is disabled', $result['message']);
        self::assertSame('disabled', $result['data']['status']);
    }
}

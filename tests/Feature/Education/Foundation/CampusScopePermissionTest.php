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

use App\Exception\BusinessException;
use App\Http\Admin\Controller\Education\Foundation\UserProfileController;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\CampusScopeService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @internal
 * @coversNothing
 */
final class CampusScopePermissionTest extends EducationAdminControllerCase
{
    public function testUserWithoutProfilePermissionCannotPageProfiles(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');

        $result = $this->get('/admin/education/foundation/user-profiles/page', [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testTeacherWithNoScopeCannotAccessCampusData(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);
        $context = new EducationUserContext(
            userId: (int) $this->user->id,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::Teacher,
            platformAccess: false,
            campusIds: [],
            currentCampusId: null
        );

        try {
            make(CampusScopeService::class)->assertCampusInScope($context, (int) $campus->id);
            self::fail('Expected teacher without scope to be rejected.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame($campus->id, $exception->getResponse()->data['campus_id']);
        }
    }

    public function testWriteControllersIncludeOperationMiddleware(): void
    {
        $attributes = (new \ReflectionClass(UserProfileController::class))->getAttributes(Middleware::class);
        $middlewares = array_map(static fn ($attribute) => $attribute->newInstance()->middleware, $attributes);

        self::assertContains(OperationMiddleware::class, $middlewares);
    }
}

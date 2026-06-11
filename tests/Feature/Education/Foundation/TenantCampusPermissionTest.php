<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Education\Foundation;

use App\Http\Admin\Controller\Education\Foundation\CampusController;
use App\Http\Admin\Controller\Education\Foundation\TenantController;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\HttpServer\Annotation\Middleware;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
final class TenantCampusPermissionTest extends EducationAdminControllerCase
{
    public function testUserWithoutTenantPermissionCannotAccessTenantPage(): void
    {
        $result = $this->get('/admin/education/foundation/tenants/page', ['token' => $this->token]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testUserWithoutCampusPermissionCannotCreateCampus(): void
    {
        $tenant = EducationTenant::query()->create([
            'name' => 'Tenant',
            'code' => 'tenant',
            'status' => 'enabled',
        ]);

        $result = $this->post('/admin/education/foundation/campuses', [
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testWriteControllersIncludeOperationMiddleware(): void
    {
        self::assertTrue($this->controllerHasOperationMiddleware(TenantController::class));
        self::assertTrue($this->controllerHasOperationMiddleware(CampusController::class));
    }

    private function controllerHasOperationMiddleware(string $controller): bool
    {
        foreach ((new ReflectionClass($controller))->getAttributes(Middleware::class) as $attribute) {
            if (($attribute->getArguments()['middleware'] ?? null) === OperationMiddleware::class) {
                return true;
            }
        }

        return false;
    }
}

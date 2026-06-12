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

use App\Http\Admin\Controller\Education\Foundation\DictionaryController;
use App\Http\Admin\Controller\Education\Foundation\FeatureFlagController;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\ResultCode;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @internal
 * @coversNothing
 */
final class DictionaryFeaturePermissionTest extends EducationAdminControllerCase
{
    public function testUserWithoutDictionaryPermissionCannotPage(): void
    {
        $this->createEducationProfile();

        $result = $this->get('/admin/education/foundation/dict-types/page', ['token' => $this->token]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testWriteControllersIncludeOperationMiddleware(): void
    {
        self::assertTrue($this->controllerHasOperationMiddleware(DictionaryController::class));
        self::assertTrue($this->controllerHasOperationMiddleware(FeatureFlagController::class));
    }

    private function controllerHasOperationMiddleware(string $controller): bool
    {
        foreach ((new \ReflectionClass($controller))->getAttributes(Middleware::class) as $attribute) {
            if (($attribute->getArguments()['middleware'] ?? null) === OperationMiddleware::class) {
                return true;
            }
        }

        return false;
    }
}

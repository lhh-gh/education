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

namespace HyperfTests\Unit\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\ConfigOwnerResolver;
use App\Service\Education\Foundation\EducationUserContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ConfigOwnerResolverTest extends TestCase
{
    public function testTenantContextCannotWriteSystemOwner(): void
    {
        $context = new EducationUserContext(1, 10, EducationRoleCode::TenantAdmin, false, [], null);

        try {
            make(ConfigOwnerResolver::class)->assertCanWriteOwner($context, 'system', null);
            self::fail('Expected tenant context writing system owner to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
        }
    }

    public function testPlatformContextCanWriteSystemOwner(): void
    {
        $context = new EducationUserContext(1, null, EducationRoleCode::PlatformOperator, true, [], null);
        $resolver = make(ConfigOwnerResolver::class);

        $resolver->assertCanWriteOwner($context, 'system', null);

        self::assertSame('system', $resolver->resolveOwnerKey('system', null));
        self::assertSame('tenant:3', $resolver->resolveOwnerKey('tenant', 3));
    }
}

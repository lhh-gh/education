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

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\AuditContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use GuzzleHttp\Psr7\ServerRequest;
use Hyperf\Context\Context;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
final class AuditContextResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        Context::destroy(ServerRequestInterface::class);
        parent::tearDown();
    }

    public function testRequestContextResolvesHeadersIpMethodAndPath(): void
    {
        Context::set(ServerRequestInterface::class, (new ServerRequest(
            'POST',
            '/admin/education/foundation/audit-logs/page',
            [
                'X-Request-Id' => 'req-header',
                'X-Forwarded-For' => '172.16.0.1, 172.16.0.2',
                'User-Agent' => 'MineAdminTest/1.0',
            ],
            null,
            '1.1',
            ['REMOTE_ADDR' => '127.0.0.1']
        ))->withAttribute('request_id', 'req-attribute'));

        $resolver = make(AuditContextResolver::class);

        self::assertSame('req-header', $resolver->resolveRequestId());
        self::assertSame('172.16.0.1', $resolver->resolveIpAddress());
        self::assertSame('MineAdminTest/1.0', $resolver->resolveUserAgent());
        self::assertSame('POST', $resolver->resolveMethod());
        self::assertSame('/admin/education/foundation/audit-logs/page', $resolver->resolvePath());
    }

    public function testContextResolvesRoleTenantAndAllowedCampus(): void
    {
        $context = new EducationUserContext(
            userId: 501,
            tenantId: 1001,
            roleCode: EducationRoleCode::AcademicStaff,
            platformAccess: false,
            campusIds: [2001],
            currentCampusId: 2001
        );

        $resolver = make(AuditContextResolver::class);

        self::assertSame('academic_staff', $resolver->resolveActorRoleCode($context));
        self::assertSame(1001, $resolver->resolveTenantId($context));
        self::assertSame(2001, $resolver->resolveCampusId($context, ['campus_id' => 2001]));
        self::assertNull($resolver->resolveCampusId($context, ['campus_id' => 2002]));
    }
}

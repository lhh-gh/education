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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\Education\Academic\GuardianService;

/**
 * @internal
 * @coversNothing
 */
final class GuardianServiceTest extends AcademicTestCase
{
    public function testDuplicateMobileReturnsConflict(): void
    {
        $tenant = $this->tenant('tenant');
        $service = make(GuardianService::class);
        $service->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian A',
            'mobile' => '13800000001',
        ], $this->context((int) $tenant->id), 901);

        try {
            $service->create([
                'tenant_id' => $tenant->id,
                'name' => 'Guardian B',
                'mobile' => '13800000001',
            ], $this->context((int) $tenant->id), 901);
            self::fail('Expected duplicate mobile to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('13800000001', $exception->getResponse()->data['mobile']);
        }
    }
}

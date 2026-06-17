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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class ConsumptionReviewAdminApiTest extends OperationApiCase
{
    public function testValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:operations:consumption-adjustment:create');
        $fixture = $this->fixture('ops_consumption_api');
        $this->createTenantProfile($fixture['tenant']);
        $account = $this->account($fixture['tenant'], $fixture['campus']);
        $original = $this->consumption($fixture['tenant'], $fixture['campus'], $account, $fixture['lesson'], 'reversed');
        $validation = $this->post('/admin/education/operations/lesson-consumptions/' . $original->id . '/adjust', ['credits' => '-1.00'], $this->tenantHeaders($fixture['tenant']));
        $business = $this->post('/admin/education/operations/lesson-consumptions/' . $original->id . '/adjust', ['credits' => '-1.00', 'reason' => 'wrong attendance status'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);
        self::assertSame(ResultCode::CONFLICT->value, $business['code']);
    }
}

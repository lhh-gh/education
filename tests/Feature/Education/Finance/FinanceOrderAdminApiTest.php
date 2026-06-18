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

namespace HyperfTests\Feature\Education\Finance;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class FinanceOrderAdminApiTest extends FinanceApiCase
{
    public function testOrderApiValidationAndBusinessFailuresMatchCatalog(): void
    {
        $this->grantPermissions('education:finance:order:create');
        $fixture = $this->financeFixture('finance_order_api');
        $this->createTenantProfile($fixture['tenant']);

        $validation = $this->post('/admin/education/finance/orders/from-enrollment', [
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $validation['code']);

        $created = $this->post('/admin/education/finance/orders/from-enrollment', [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('pending', $created['data']['status']);

        $duplicate = $this->post('/admin/education/finance/orders/from-enrollment', [
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);
        self::assertSame('finance order already exists for enrollment', $duplicate['message']);
    }
}

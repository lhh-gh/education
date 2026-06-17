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

namespace HyperfTests\Unit\Education\Finance;

use App\Exception\BusinessException;
use App\Model\Education\Finance\EducationFinanceOrderItem;
use App\Service\Education\Finance\FinanceOrderService;

/**
 * @internal
 * @coversNothing
 */
final class FinanceOrderServiceTest extends FinanceTestCase
{
    public function testEnrollmentCreatesOneFinanceOrder(): void
    {
        $tenant = $this->tenant('fin_order');
        $campus = $this->campus($tenant);
        $student = $this->studentFixture($tenant, $campus);
        $package = $this->packageFixture($tenant, $campus);
        $enrollment = $this->pendingEnrollmentFixture($tenant, $campus, $student, $package);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9201);
        $service = make(FinanceOrderService::class);

        $order = $service->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);

        self::assertSame('pending', $order['status']);
        self::assertSame(240000, $order['total_amount_cents']);
        self::assertSame(1, EducationFinanceOrderItem::query()->where('order_id', $order['order_id'])->count());

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('finance order already exists for enrollment');
        $service->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);
    }
}

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

use App\Model\Education\Academic\EducationEnrollment;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Finance\FinanceOrderService;
use App\Service\Education\Finance\OfflineCollectionService;

/**
 * @internal
 * @coversNothing
 */
final class OfflineCollectionServiceTest extends FinanceTestCase
{
    public function testFullOfflinePaymentConfirmsPendingEnrollment(): void
    {
        $tenant = $this->tenant('fin_offline');
        $campus = $this->campus($tenant);
        $student = $this->studentFixture($tenant, $campus);
        $package = $this->packageFixture($tenant, $campus);
        $enrollment = $this->pendingEnrollmentFixture($tenant, $campus, $student, $package);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9301);
        $order = make(FinanceOrderService::class)->createFromEnrollment((int) $enrollment->id, [
            'student_id' => (int) $student->id,
            'items' => [['item_type' => 'lesson_package', 'item_name' => '24 lessons', 'quantity' => '1.00', 'unit_amount_cents' => 240000]],
        ], $context);

        $result = make(OfflineCollectionService::class)->confirm([
            'order_id' => $order['order_id'],
            'channel_code' => 'offline_cash',
            'payment_no' => 'OFF-001',
            'amount_cents' => 240000,
            'payer_name' => 'Guardian',
        ], $context);

        self::assertSame('paid', $result['order_status']);
        self::assertSame('confirmed', EducationEnrollment::query()->find($enrollment->id)->status);
        self::assertSame('24.00', EducationStudentCourseAccount::query()->where('student_id', $student->id)->first()->available_units);
    }
}

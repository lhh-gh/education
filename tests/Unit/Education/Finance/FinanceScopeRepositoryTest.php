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

use App\Model\Education\Finance\EducationPaymentChannel;
use App\Model\Education\Finance\EducationReceipt;
use App\Model\Education\Finance\EducationRefundRequest;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Finance\PaymentChannelRepository;
use App\Repository\Education\Finance\ReceiptRepository;
use App\Repository\Education\Finance\RefundRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class FinanceScopeRepositoryTest extends FinanceTestCase
{
    public function testPlatformCurrentCampusFiltersReceiptsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('fin_scope_receipt');
        $visible = EducationReceipt::query()->create($this->receiptData($tenantId, $campusId, 'RC-SCOPE-001'));
        $other = EducationReceipt::query()->create($this->receiptData($tenantId, $otherCampusId, 'RC-SCOPE-002'));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(ReceiptRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(ReceiptRepository::class)->lock((int) $visible->id, $context));
        self::assertNull(make(ReceiptRepository::class)->lock((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersRefundRequestsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('fin_scope_refund');
        $visible = EducationRefundRequest::query()->create($this->refundData($tenantId, $campusId, 'FR-SCOPE-001'));
        $other = EducationRefundRequest::query()->create($this->refundData($tenantId, $otherCampusId, 'FR-SCOPE-002'));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(RefundRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(RefundRepository::class)->lockRequest((int) $visible->id, $context));
        self::assertNull(make(RefundRepository::class)->lockRequest((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersPaymentChannelsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('fin_scope_channel');
        $visible = EducationPaymentChannel::query()->create($this->channelData($tenantId, $campusId, 'cash_a'));
        EducationPaymentChannel::query()->create($this->channelData($tenantId, $otherCampusId, 'cash_b'));

        $list = make(PaymentChannelRepository::class)->list([
            'status' => 'enabled',
        ], $this->platformContext($tenantId, $campusId));

        self::assertCount(1, $list);
        self::assertSame((int) $visible->id, (int) $list[0]['id']);
    }

    private function tenantCampusPair(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main_' . $code);
        $otherCampus = $this->campus($tenant, 'branch_' . $code);

        return [(int) $tenant->id, (int) $campus->id, (int) $otherCampus->id];
    }

    private function platformContext(int $tenantId, int $campusId): EducationUserContext
    {
        return new EducationUserContext(
            userId: 1,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $campusId
        );
    }

    private function receiptData(int $tenantId, int $campusId, string $receiptNo): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'receipt_no' => $receiptNo,
            'order_id' => 101,
            'student_id' => 201,
            'amount_cents' => 120000,
            'status' => 'issued',
            'issued_by' => 9001,
            'issued_at' => '2026-06-20 10:00:00',
        ];
    }

    private function refundData(int $tenantId, int $campusId, string $refundNo): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'order_id' => 301,
            'payment_record_id' => 401,
            'refund_no' => $refundNo,
            'refund_amount_cents' => 50000,
            'reason' => 'student changed plan',
            'status' => 'pending',
            'requested_by' => 9001,
        ];
    }

    private function channelData(int $tenantId, int $campusId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'channel_code' => $code,
            'channel_name' => 'Cash',
            'channel_type' => 'offline',
            'config_json' => ['cashier_required' => true],
            'status' => 'enabled',
            'sort_order' => 1,
        ];
    }
}

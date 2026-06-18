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

namespace HyperfTests\Feature\Education\Group;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class ContractAdminApiTest extends GroupApiCase
{
    public function testContractApiValidationDuplicatePageAndSubmitReview(): void
    {
        $fixture = $this->groupFixture('group_contract_api');
        $this->createTenantProfile($fixture['tenant'], 'tenant_admin', $fixture['campus']);
        $this->grantPermissions(
            'education:group:contract:create',
            'education:group:contract:page',
            'education:group:contract:submit-review'
        );

        $invalid = $this->post('/admin/education/group/contracts', [
            'contract_no' => 'CT-BAD',
            'contract_type' => 'lease',
            'title' => 'Bad Contract',
            'counterparty_name' => 'Landlord',
            'amount_cents' => 12000000,
            'start_date' => '2027-06-10',
            'end_date' => '2026-06-09',
        ], $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);

        $payload = [
            'contract_no' => 'CT202606100001',
            'contract_type' => 'lease',
            'title' => 'Campus Lease',
            'counterparty_name' => 'Landlord',
            'amount_cents' => 12000000,
            'start_date' => '2026-06-10',
            'end_date' => '2027-06-09',
        ];
        $created = $this->post('/admin/education/group/contracts', $payload, $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('draft', $created['data']['status']);

        $duplicate = $this->post('/admin/education/group/contracts', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::CONFLICT->value, $duplicate['code']);

        $page = $this->get('/admin/education/group/contracts/page', [], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame(1, $page['data']['total']);

        $reviewing = $this->post('/admin/education/group/contracts/' . $created['data']['contract_id'] . '/submit-review', [], $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::SUCCESS->value, $reviewing['code']);
        self::assertSame('reviewing', $reviewing['data']['status']);
    }
}

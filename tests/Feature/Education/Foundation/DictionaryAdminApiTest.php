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

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Education\Foundation\EducationTenant;

/**
 * @internal
 * @coversNothing
 */
final class DictionaryAdminApiTest extends EducationAdminControllerCase
{
    public function testPlatformAdminCanCreateTypeAndItem(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions(
            'education:foundation:dictionary:create',
            'education:foundation:dictionary-item:create',
            'education:foundation:dictionary-item:lookup'
        );

        $typeResult = $this->post('/admin/education/foundation/dict-types', [
            'owner_type' => 'system',
            'code' => 'student_source',
            'name' => 'Student source',
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $typeResult['code']);
        self::assertSame('system', $typeResult['data']['owner_key']);

        $itemResult = $this->post('/admin/education/foundation/dict-items', [
            'dict_type_id' => $typeResult['data']['id'],
            'label' => 'Referral',
            'value' => 'referral',
            'color' => 'green',
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $itemResult['code']);

        $lookup = $this->get('/admin/education/foundation/dictionaries/student_source/items', ['token' => $this->token]);

        self::assertSame(ResultCode::SUCCESS->value, $lookup['code']);
        self::assertSame('student_source', $lookup['data']['dict_code']);
        self::assertSame('referral', $lookup['data']['items'][0]['value']);
    }

    public function testTenantAdminCanCreateOnlyTenantDictionary(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant', 'code' => 'tenant', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenant->id, 'tenant_admin');
        $this->grantPermissions('education:foundation:dictionary:create');

        $tenantResult = $this->post('/admin/education/foundation/dict-types', [
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'code' => 'student_source',
            'name' => 'Student source',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::SUCCESS->value, $tenantResult['code']);
        self::assertSame('tenant:' . $tenant->id, $tenantResult['data']['owner_key']);

        $systemResult = $this->post('/admin/education/foundation/dict-types', [
            'owner_type' => 'system',
            'code' => 'blocked_system',
            'name' => 'Blocked system',
            'status' => 'enabled',
        ], $this->authHeaders(['X-Tenant-Id' => (string) $tenant->id]));

        self::assertSame(ResultCode::FORBIDDEN->value, $systemResult['code']);
    }

    public function testDuplicateDictionaryCodeReturnsConflict(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions('education:foundation:dictionary:create');
        EducationDictType::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'code' => 'student_source',
            'name' => 'Student source',
            'status' => 'enabled',
        ]);

        $result = $this->post('/admin/education/foundation/dict-types', [
            'owner_type' => 'system',
            'code' => 'student_source',
            'name' => 'Student source again',
            'status' => 'enabled',
        ], $this->authHeaders());

        self::assertSame(ResultCode::CONFLICT->value, $result['code']);
        self::assertSame('dictionary code already exists', $result['message']);
        self::assertSame('student_source', $result['data']['code']);
    }
}

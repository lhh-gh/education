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

use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\CampusService;
use App\Service\Education\Foundation\DictionaryService;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\FeatureFlagService;
use App\Service\Education\Foundation\TenantService;
use Hyperf\Context\Context;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
final class FoundationAuditWriteIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy(ServerRequestInterface::class);
        EducationAuditLog::query()->whereRaw('1=1')->delete();
        EducationDictItem::query()->whereRaw('1=1')->forceDelete();
        EducationDictType::query()->whereRaw('1=1')->forceDelete();
        EducationFeatureFlag::query()->whereRaw('1=1')->forceDelete();
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    public function testTenantUpdateDispatchesAuditLog(): void
    {
        $tenant = EducationTenant::query()->create([
            'name' => 'Tenant A',
            'code' => 'tenant_a',
            'status' => 'enabled',
        ]);

        make(TenantService::class)->updateTenant((int) $tenant->id, [
            'name' => 'Tenant A Plus',
            'code' => 'tenant_a',
            'status' => 'enabled',
            'updated_by' => 501,
        ], $this->platformContext());

        $log = $this->latestLog('education.foundation.tenant.updated');

        self::assertSame('tenant', $log->business_type);
        self::assertSame((string) $tenant->id, $log->business_id);
        self::assertSame('Tenant A', $log->before_snapshot['name']);
        self::assertSame('Tenant A Plus', $log->after_snapshot['name']);
    }

    public function testCampusStatusChangeDispatchesAuditLog(): void
    {
        $tenant = $this->createTenant();
        $campus = EducationCampus::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Campus',
            'code' => 'main',
            'status' => 'enabled',
        ]);

        make(CampusService::class)->changeStatus(
            (int) $tenant->id,
            (int) $campus->id,
            'disabled',
            501,
            $this->tenantContext((int) $tenant->id)
        );

        $log = $this->latestLog('education.foundation.campus.status_changed');

        self::assertSame('campus', $log->business_type);
        self::assertSame((int) $tenant->id, $log->tenant_id);
        self::assertSame((int) $campus->id, $log->campus_id);
        self::assertSame('enabled', $log->before_snapshot['status']);
        self::assertSame('disabled', $log->after_snapshot['status']);
    }

    public function testDictionaryItemUpdateDispatchesAuditLog(): void
    {
        $tenant = $this->createTenant();
        $type = EducationDictType::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'owner_key' => 'tenant:' . $tenant->id,
            'code' => 'student_status',
            'name' => 'Student status',
            'status' => 'enabled',
        ]);
        $item = EducationDictItem::query()->create([
            'dict_type_id' => $type->id,
            'owner_key' => $type->owner_key,
            'dict_code' => $type->code,
            'label' => 'Active',
            'value' => 'active',
            'status' => 'enabled',
        ]);

        make(DictionaryService::class)->updateItem((int) $item->id, [
            'label' => 'Active Student',
            'value' => 'active',
            'status' => 'enabled',
        ], $this->tenantContext((int) $tenant->id), 501);

        $log = $this->latestLog('education.foundation.dict_item.updated');

        self::assertSame('dict_item', $log->business_type);
        self::assertSame((string) $item->id, $log->business_id);
        self::assertSame('Active', $log->before_snapshot['label']);
        self::assertSame('Active Student', $log->after_snapshot['label']);
    }

    public function testFeatureFlagUpdateDispatchesAuditLog(): void
    {
        $tenant = $this->createTenant();
        $flag = EducationFeatureFlag::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => $tenant->id,
            'owner_key' => 'tenant:' . $tenant->id,
            'feature_code' => 'education.test.feature',
            'feature_name' => 'Test feature',
            'enabled' => false,
            'status' => 'enabled',
        ]);

        make(FeatureFlagService::class)->updateFlag((int) $flag->id, [
            'feature_name' => 'Test feature',
            'enabled' => true,
            'status' => 'enabled',
        ], $this->tenantContext((int) $tenant->id), 501);

        $log = $this->latestLog('education.foundation.feature_flag.updated');

        self::assertSame('feature_flag', $log->business_type);
        self::assertSame((string) $flag->id, $log->business_id);
        self::assertFalse($log->before_snapshot['enabled']);
        self::assertTrue($log->after_snapshot['enabled']);
    }

    private function createTenant(): EducationTenant
    {
        /** @var EducationTenant $tenant */
        $tenant = EducationTenant::query()->create([
            'name' => 'Tenant A',
            'code' => 'tenant_a',
            'status' => 'enabled',
        ]);

        return $tenant;
    }

    private function latestLog(string $action): EducationAuditLog
    {
        $log = EducationAuditLog::query()
            ->where('action', $action)
            ->orderByDesc('id')
            ->first();

        self::assertInstanceOf(EducationAuditLog::class, $log);

        return $log;
    }

    private function platformContext(): EducationUserContext
    {
        return new EducationUserContext(501, null, EducationRoleCode::PlatformOperator, true, [], null);
    }

    private function tenantContext(int $tenantId): EducationUserContext
    {
        return new EducationUserContext(501, $tenantId, EducationRoleCode::TenantAdmin, false, [], null);
    }
}

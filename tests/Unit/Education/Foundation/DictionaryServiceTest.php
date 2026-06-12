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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\DictionaryService;
use App\Service\Education\Foundation\EducationUserContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DictionaryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationDictItem::query()->whereRaw('1=1')->forceDelete();
        EducationDictType::query()->whereRaw('1=1')->forceDelete();
    }

    public function testItemsReturnTenantDictionaryBeforeSystemDefault(): void
    {
        $system = $this->createType('system', null, 'system', 'common_status');
        $this->createItem($system, 'Enabled', 'enabled', 10);
        $this->createItem($system, 'Disabled', 'disabled', 20);
        $tenant = $this->createType('tenant', 1, 'tenant:1', 'common_status');
        $this->createItem($tenant, 'Open', 'open', 10);
        $this->createItem($tenant, 'Closed', 'closed', 20);

        $service = make(DictionaryService::class);

        self::assertSame(['open', 'closed'], array_column($service->items('common_status', 1), 'value'));
        self::assertSame(['enabled', 'disabled'], array_column($service->items('common_status', 2), 'value'));
    }

    public function testDuplicateItemValueReturnsConflict(): void
    {
        $type = $this->createType('system', null, 'system', 'common_status');
        $context = new EducationUserContext(1, null, EducationRoleCode::PlatformOperator, true, [], null);
        $service = make(DictionaryService::class);
        $service->createItem([
            'dict_type_id' => $type->id,
            'label' => 'Enabled',
            'value' => 'enabled',
        ], $context, null);

        try {
            $service->createItem([
                'dict_type_id' => $type->id,
                'label' => 'Enabled again',
                'value' => 'enabled',
            ], $context, null);
            self::fail('Expected duplicate item value to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('enabled', $exception->getResponse()->data['value']);
        }
    }

    private function createType(string $ownerType, ?int $tenantId, string $ownerKey, string $code): EducationDictType
    {
        return EducationDictType::query()->create([
            'owner_type' => $ownerType,
            'tenant_id' => $tenantId,
            'owner_key' => $ownerKey,
            'code' => $code,
            'name' => 'Status',
            'status' => 'enabled',
        ]);
    }

    private function createItem(EducationDictType $type, string $label, string $value, int $sortOrder): void
    {
        EducationDictItem::query()->create([
            'dict_type_id' => $type->id,
            'owner_key' => $type->owner_key,
            'dict_code' => $type->code,
            'label' => $label,
            'value' => $value,
            'sort_order' => $sortOrder,
            'status' => 'enabled',
        ]);
    }
}

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

use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use App\Repository\Education\Foundation\DictItemRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DictItemRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationDictItem::query()->whereRaw('1=1')->forceDelete();
        EducationDictType::query()->whereRaw('1=1')->forceDelete();
    }

    public function testEnabledItemsFiltersStatusAndSorts(): void
    {
        $type = EducationDictType::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'code' => 'attendance_status',
            'name' => 'Attendance',
            'status' => 'enabled',
        ]);
        EducationDictItem::query()->create([
            'dict_type_id' => $type->id,
            'owner_key' => 'system',
            'dict_code' => 'attendance_status',
            'label' => 'Disabled',
            'value' => 'disabled',
            'sort_order' => 1,
            'status' => 'disabled',
        ]);
        EducationDictItem::query()->create([
            'dict_type_id' => $type->id,
            'owner_key' => 'system',
            'dict_code' => 'attendance_status',
            'label' => 'Second',
            'value' => 'second',
            'sort_order' => 20,
            'status' => 'enabled',
        ]);
        EducationDictItem::query()->create([
            'dict_type_id' => $type->id,
            'owner_key' => 'system',
            'dict_code' => 'attendance_status',
            'label' => 'First',
            'value' => 'first',
            'sort_order' => 10,
            'status' => 'enabled',
        ]);

        $repository = make(DictItemRepository::class);
        $items = $repository->enabledItems('system', 'attendance_status');

        self::assertSame(['first', 'second'], $items->pluck('value')->all());
        self::assertTrue($repository->existsValue((int) $type->id, 'first'));
        self::assertFalse($repository->existsValue((int) $type->id, 'missing'));
    }
}

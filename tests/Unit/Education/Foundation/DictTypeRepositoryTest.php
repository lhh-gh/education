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
use App\Repository\Education\Foundation\DictTypeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DictTypeRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationDictItem::query()->whereRaw('1=1')->forceDelete();
        EducationDictType::query()->whereRaw('1=1')->forceDelete();
    }

    public function testExistsByOwnerCodeIsOwnerScoped(): void
    {
        EducationDictType::query()->create([
            'owner_type' => 'system',
            'owner_key' => 'system',
            'code' => 'common_status',
            'name' => 'System status',
            'status' => 'enabled',
        ]);
        $tenantType = EducationDictType::query()->create([
            'owner_type' => 'tenant',
            'tenant_id' => 1,
            'owner_key' => 'tenant:1',
            'code' => 'common_status',
            'name' => 'Tenant status',
            'status' => 'enabled',
        ]);

        $repository = make(DictTypeRepository::class);

        self::assertTrue($repository->existsByOwnerCode('system', 'common_status'));
        self::assertTrue($repository->existsByOwnerCode('tenant:1', 'common_status'));
        self::assertFalse($repository->existsByOwnerCode('tenant:2', 'common_status'));
        self::assertFalse($repository->existsByOwnerCode('tenant:1', 'common_status', (int) $tenantType->id));
        self::assertSame((int) $tenantType->id, (int) $repository->findByOwnerCode('tenant:1', 'common_status')?->id);
    }
}

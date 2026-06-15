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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\ClassroomService;

/**
 * @internal
 * @coversNothing
 */
final class ClassroomServiceTest extends AcademicTestCase
{
    public function testCreateRejectsCampusOutsideContext(): void
    {
        $tenant = $this->tenant('tenant');
        $allowedCampus = $this->campus($tenant, 'allowed');
        $blockedCampus = $this->campus($tenant, 'blocked');

        try {
            make(ClassroomService::class)->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $blockedCampus->id,
                'code' => 'A101',
                'name' => 'Blocked Room',
            ], $this->context((int) $tenant->id, EducationRoleCode::AcademicStaff, [(int) $allowedCampus->id]), 901);
            self::fail('Expected campus outside context to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame((int) $blockedCampus->id, $exception->getResponse()->data['campus_id']);
        }
    }
}

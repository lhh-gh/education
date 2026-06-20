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
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Academic\GuardianService;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class GuardianServiceTest extends AcademicTestCase
{
    public function testPageStudentCountRespectsPlatformCurrentCampus(): void
    {
        $tenant = $this->tenant('guardian_service_scope');
        $campusA = $this->campus($tenant, 'main_a');
        $campusB = $this->campus($tenant, 'main_b');
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian A',
            'mobile' => '13800003001',
        ]);
        $studentA = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusA->id,
            'student_no' => 'S-GS-A',
            'name' => 'Student A',
        ]);
        $studentB = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campusB->id,
            'student_no' => 'S-GS-B',
            'name' => 'Student B',
        ]);
        foreach ([$studentA, $studentB] as $student) {
            EducationStudentGuardian::query()->create([
                'tenant_id' => $tenant->id,
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'relation' => 'mother',
            ]);
        }

        $result = make(GuardianService::class)->page([], new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $result['total']);
        self::assertSame(1, $result['list'][0]['student_count']);
    }

    public function testDuplicateMobileReturnsConflict(): void
    {
        $tenant = $this->tenant('tenant');
        $service = make(GuardianService::class);
        $service->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian A',
            'mobile' => '13800000001',
        ], $this->context((int) $tenant->id), 901);

        try {
            $service->create([
                'tenant_id' => $tenant->id,
                'name' => 'Guardian B',
                'mobile' => '13800000001',
            ], $this->context((int) $tenant->id), 901);
            self::fail('Expected duplicate mobile to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame('13800000001', $exception->getResponse()->data['mobile']);
        }
    }
}

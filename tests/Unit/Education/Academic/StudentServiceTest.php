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
use App\Service\Education\Academic\StudentService;

/**
 * @internal
 * @coversNothing
 */
final class StudentServiceTest extends AcademicTestCase
{
    public function testSaveGuardiansRejectsGuardianOutsideTenant(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenantA->id,
            'campus_id' => $campusA->id,
            'student_no' => 'S001',
            'name' => 'Student A',
        ]);
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Guardian B',
            'mobile' => '13800000002',
        ]);

        try {
            make(StudentService::class)->saveGuardians((int) $student->id, [
                ['guardian_id' => $guardian->id, 'relation' => 'guardian'],
            ], $this->context((int) $tenantA->id), 901);
            self::fail('Expected outside tenant guardian to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::FORBIDDEN, $exception->getResponse()->code);
            self::assertSame((int) $guardian->id, $exception->getResponse()->data['guardian_id']);
        }
    }

    public function testSaveGuardiansNormalizesPrimaryRelation(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $student = EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_no' => 'S001',
            'name' => 'Student',
        ]);
        $mother = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Mother',
            'mobile' => '13800000001',
        ]);
        $father = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Father',
            'mobile' => '13800000002',
        ]);

        $relations = make(StudentService::class)->saveGuardians((int) $student->id, [
            ['guardian_id' => $mother->id, 'relation' => 'mother', 'is_primary' => false],
            ['guardian_id' => $father->id, 'relation' => 'father', 'is_primary' => false],
        ], $this->context((int) $tenant->id), 901);

        $primaryRelations = array_values(array_filter($relations, static fn (array $relation): bool => (bool) $relation['is_primary']));
        self::assertCount(1, $primaryRelations);
        self::assertSame((int) $mother->id, (int) $primaryRelations[0]['guardian_id']);
    }
}

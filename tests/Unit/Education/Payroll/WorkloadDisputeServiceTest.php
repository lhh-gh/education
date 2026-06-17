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

namespace HyperfTests\Unit\Education\Payroll;

use App\Exception\BusinessException;
use App\Service\Education\Payroll\WorkloadDisputeService;

/**
 * @internal
 * @coversNothing
 */
final class WorkloadDisputeServiceTest extends PayrollTestCase
{
    public function testTeacherCanDisputeOwnWorkloadOnly(): void
    {
        $tenant = $this->tenant('pay_dispute');
        $campus = $this->campus($tenant);
        $teacher = $this->teacherFixture($tenant, $campus);
        $otherTeacher = $this->teacherFixture($tenant, $campus, 'Other Teacher');
        $ownWorkload = $this->workloadFixture($tenant, $campus, $teacher);
        $otherWorkload = $this->workloadFixture($tenant, $campus, $otherTeacher);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id], userId: 9405);
        $service = make(WorkloadDisputeService::class);

        $result = $service->submitForTeacher((int) $teacher->id, [
            'source_workload_id' => (int) $ownWorkload->id,
            'dispute_type' => 'missing_workload',
            'content' => 'substitute class is missing',
        ], $context);

        self::assertSame('pending', $result['status']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('workload does not belong to current teacher');
        $service->submitForTeacher((int) $teacher->id, [
            'source_workload_id' => (int) $otherWorkload->id,
            'dispute_type' => 'missing_workload',
            'content' => 'other teacher workload',
        ], $context);
    }
}

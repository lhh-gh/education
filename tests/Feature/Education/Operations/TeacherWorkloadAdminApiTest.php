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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;

/**
 * @internal
 * @coversNothing
 */
final class TeacherWorkloadAdminApiTest extends OperationApiCase
{
    public function testPlatformUserCanPageTeacherWorkloadWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:teacher-workload:report');
        $fixture = $this->fixture('ops_teacher_workload_page_platform');
        $this->createEducationProfile();
        EducationTeacherWorkloadRecord::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'teacher_id' => $this->user->id, 'lesson_id' => $fixture['lesson']->id, 'workload_type' => 'main', 'lesson_type' => 'normal', 'credits' => '1.00', 'student_count' => 1, 'present_count' => 1, 'leave_count' => 0, 'absent_count' => 0, 'recorded_at' => '2026-06-10 11:00:00']);

        $report = $this->get('/admin/education/operations/reports/teacher-workloads?teacher_id=' . $this->user->id, [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $report['code']);
        self::assertSame(1, $report['data']['total']);
        self::assertSame(1, $report['data']['summary']['row_count']);
        self::assertSame('1.00', $report['data']['summary']['credits']);
    }

    public function testPlatformUserCanOpenTeacherWorkloadSummaryWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:teacher-workload:report');
        $fixture = $this->fixture('ops_teacher_workload_summary_platform');
        $this->createEducationProfile();
        EducationTeacherWorkloadRecord::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'teacher_id' => $this->user->id, 'lesson_id' => $fixture['lesson']->id, 'workload_type' => 'main', 'lesson_type' => 'normal', 'credits' => '2.50', 'student_count' => 3, 'present_count' => 2, 'leave_count' => 1, 'absent_count' => 0, 'recorded_at' => '2026-06-10 11:00:00']);

        $summary = $this->get('/admin/education/operations/reports/teacher-workloads/summary?teacher_id=' . $this->user->id, [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $summary['code']);
        self::assertSame(1, $summary['data']['row_count']);
        self::assertSame('2.50', $summary['data']['total_credits']);
        self::assertSame(3, $summary['data']['student_count']);
        self::assertSame(2, $summary['data']['present_count']);
    }
}

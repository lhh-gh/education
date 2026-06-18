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

use App\Model\Education\Academic\EducationLeaveRequest;
use App\Repository\Education\Academic\LeaveRequestRepository;

/**
 * @internal
 * @coversNothing
 */
final class LeaveRequestRepositoryTest extends AcademicTestCase
{
    public function testPageFiltersByTenantCampusStudentStatusAndKeyword(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $visible = $this->leaveRequest((int) $tenantA->id, (int) $campusA->id, 'LEA001', 101, 'pending', 'medical leave', 7001);
        $this->leaveRequest((int) $tenantA->id, (int) $campusA->id, 'LEA002', 102, 'pending', 'medical leave', 7002);
        $this->leaveRequest((int) $tenantA->id, (int) $campusA->id, 'LEA003', 101, 'approved', 'medical leave', 7003);
        $this->leaveRequest((int) $tenantB->id, (int) $campusB->id, 'LEA004', 101, 'pending', 'medical leave', 7001);

        $result = make(LeaveRequestRepository::class)->page([
            'campus_id' => $campusA->id,
            'student_id' => 101,
            'status' => 'pending',
            'keyword' => 'medical',
        ], $this->context((int) $tenantA->id, campusIds: [(int) $campusA->id]));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testExistsForLessonStudentDetectsDuplicate(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $leave = $this->leaveRequest((int) $tenant->id, (int) $campus->id, 'LEA001', 101, 'pending', 'sick', 9001);

        $repository = make(LeaveRequestRepository::class);

        self::assertTrue($repository->existsForLessonStudent(9001, (int) $tenant->id));
        self::assertFalse($repository->existsForLessonStudent(9001, (int) $tenant->id, (int) $leave->id));
    }

    private function leaveRequest(int $tenantId, int $campusId, string $leaveNo, int $studentId, string $status, string $reason, int $lessonStudentId = 7001): EducationLeaveRequest
    {
        return EducationLeaveRequest::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'leave_no' => $leaveNo,
            'source' => 'staff',
            'leave_type' => 'sick',
            'lesson_id' => $lessonStudentId + 1000,
            'lesson_student_id' => $lessonStudentId,
            'class_id' => 301,
            'course_id' => 401,
            'student_id' => $studentId,
            'account_id' => 601,
            'reason' => $reason,
            'status' => $status,
            'makeup_required' => true,
        ]);
    }
}

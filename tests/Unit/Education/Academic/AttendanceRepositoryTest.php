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

use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\AttendanceRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class AttendanceRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersLessonPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('attendance_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = $this->lesson((int) $tenant->id, (int) $campusA->id, 501, 'scheduled', '2026-06-16 09:00:00');
        $this->lesson((int) $tenant->id, (int) $campusB->id, 502, 'scheduled', '2026-06-16 09:00:00');

        $result = make(AttendanceRepository::class)->lessonPage([], new EducationUserContext(
            userId: 1,
            tenantId: (int) $tenant->id,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: (int) $campusA->id
        ));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testLessonPageFiltersByTenantCampusTeacherStatusAndRange(): void
    {
        $tenantA = $this->tenant('tenant_a');
        $tenantB = $this->tenant('tenant_b');
        $campusA = $this->campus($tenantA, 'main_a');
        $campusB = $this->campus($tenantB, 'main_b');
        $visible = $this->lesson((int) $tenantA->id, (int) $campusA->id, 501, 'scheduled', '2026-06-16 09:00:00');
        $this->lesson((int) $tenantA->id, (int) $campusA->id, 502, 'scheduled', '2026-06-16 09:00:00');
        $this->lesson((int) $tenantB->id, (int) $campusB->id, 501, 'scheduled', '2026-06-16 09:00:00');
        $this->lesson((int) $tenantA->id, (int) $campusA->id, 501, 'cancelled', '2026-06-16 09:00:00');

        $result = make(AttendanceRepository::class)->lessonPage([
            'campus_id' => $campusA->id,
            'teacher_id' => 501,
            'status' => 'scheduled',
            'start_at' => '2026-06-16 00:00:00',
            'end_at' => '2026-06-17 00:00:00',
        ], $this->context((int) $tenantA->id, campusIds: [(int) $campusA->id]));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testExistingByLessonStudentIdsReturnsIdempotencyRows(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        EducationLessonAttendance::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => 1001,
            'lesson_student_id' => 2001,
            'class_id' => 3001,
            'course_id' => 4001,
            'student_id' => 5001,
            'account_id' => 6001,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'planned_units' => '1.00',
            'consumed_units' => '1.00',
            'consumption_status' => 'active',
            'attendance_batch_no' => 'ATT001',
        ]);

        $rows = make(AttendanceRepository::class)->existingByLessonStudentIds([2001, 9999], (int) $tenant->id);

        self::assertCount(1, $rows);
        self::assertSame(2001, (int) $rows[0]['lesson_student_id']);
    }

    private function lesson(int $tenantId, int $campusId, int $teacherId, string $status, string $startAt): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_no' => uniqid('L'),
            'class_id' => 301,
            'course_id' => 401,
            'teacher_id' => $teacherId,
            'title' => 'Drawing',
            'start_at' => $startAt,
            'end_at' => '2026-06-16 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => $status,
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class',
            'course_name_snapshot' => 'Course',
            'teacher_name_snapshot' => 'Teacher',
        ]);
    }
}

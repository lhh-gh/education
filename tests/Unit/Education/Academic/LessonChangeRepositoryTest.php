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

use App\Model\Education\Academic\EducationLessonChangeRecord;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\LessonChangeRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class LessonChangeRepositoryTest extends AcademicTestCase
{
    public function testPlatformContextCampusFiltersPageWithoutLocalFilters(): void
    {
        $tenant = $this->tenant('change_platform_scope');
        $campusA = $this->campus($tenant, 'scope_a');
        $campusB = $this->campus($tenant, 'scope_b');
        $visible = $this->changeRecord((int) $tenant->id, (int) $campusA->id, 'CHG-A', 'makeup', 'confirmed', 9001, 9101);
        $this->changeRecord((int) $tenant->id, (int) $campusB->id, 'CHG-B', 'makeup', 'confirmed', 9002, 9102);

        $result = make(LessonChangeRepository::class)->page([], new EducationUserContext(
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

    public function testPageFiltersByTypeStatusSourceAndTargetLesson(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $visible = $this->changeRecord((int) $tenant->id, (int) $campus->id, 'CHG001', 'makeup', 'confirmed', 9001, 9101);
        $this->changeRecord((int) $tenant->id, (int) $campus->id, 'CHG002', 'reschedule', 'confirmed', 9001, 9101);
        $this->changeRecord((int) $tenant->id, (int) $campus->id, 'CHG003', 'makeup', 'cancelled', 9001, 9101);
        $this->changeRecord((int) $tenant->id, (int) $campus->id, 'CHG004', 'makeup', 'confirmed', 9002, 9102);

        $result = make(LessonChangeRepository::class)->page([
            'campus_id' => $campus->id,
            'change_type' => 'makeup',
            'status' => 'confirmed',
            'source_lesson_id' => 9001,
            'target_lesson_id' => 9101,
        ], $this->context((int) $tenant->id, campusIds: [(int) $campus->id]));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    public function testHasMakeupForLeaveDetectsExistingMakeup(): void
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $this->changeRecord((int) $tenant->id, (int) $campus->id, 'CHG001', 'makeup', 'confirmed', 9001, 9101, 801);

        self::assertTrue(make(LessonChangeRepository::class)->hasMakeupForLeave(801, (int) $tenant->id));
        self::assertFalse(make(LessonChangeRepository::class)->hasMakeupForLeave(802, (int) $tenant->id));
    }

    private function changeRecord(int $tenantId, int $campusId, string $changeNo, string $type, string $status, int $sourceLessonId, int $targetLessonId, ?int $leaveRequestId = null): EducationLessonChangeRecord
    {
        return EducationLessonChangeRecord::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'change_no' => $changeNo,
            'change_type' => $type,
            'status' => $status,
            'leave_request_id' => $leaveRequestId,
            'source_lesson_id' => $sourceLessonId,
            'target_lesson_id' => $targetLessonId,
            'class_id' => 301,
            'course_id' => 401,
            'source_start_at' => '2026-06-16 09:00:00',
            'source_end_at' => '2026-06-16 10:00:00',
            'target_start_at' => '2026-06-17 09:00:00',
            'target_end_at' => '2026-06-17 10:00:00',
            'lesson_units' => '1.00',
            'reason' => 'change',
        ]);
    }
}

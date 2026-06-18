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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\NoticeRepository;

/**
 * @internal
 * @coversNothing
 */
final class NoticeRepositoryTest extends AcademicTestCase
{
    public function testDraftCreationGeneratesNoticeNo(): void
    {
        $fixture = $this->fixture();

        $notice = make(NoticeRepository::class)->createDraft([
            'campus_id' => $fixture['campus_id'],
            'notice_type' => 'academic',
            'target_type' => 'class',
            'target_id' => $fixture['class_id'],
            'title' => 'Class reminder',
            'content' => 'Bring tools.',
            'priority' => 'important',
        ], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertStringStartsWith('NOT', $notice->notice_no);
        self::assertSame('draft', $notice->status);
        self::assertSame(0, (int) $notice->receipt_count);
    }

    public function testAdminPageRespectsCampusScope(): void
    {
        $fixture = $this->fixture();
        $visible = $this->notice($fixture, 'Visible notice');
        $otherCampus = $this->campus($fixture['tenant'], 'branch');
        EducationNotice::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'notice_no' => uniqid('NOT', false),
            'notice_type' => 'academic',
            'target_type' => 'campus',
            'target_id' => $otherCampus->id,
            'title' => 'Hidden notice',
            'content' => 'Hidden.',
            'priority' => 'normal',
            'status' => 'draft',
        ]);

        $result = make(NoticeRepository::class)->pageAdmin([], 1, 20, $this->context($fixture['tenant_id'], EducationRoleCode::AcademicStaff, [$fixture['campus_id']]));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('notice_repository');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-NR', 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-NR', 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-NR', 'name' => 'Student', 'status' => 'enabled']);

        return ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'class_id' => (int) $class->id, 'student_id' => (int) $student->id];
    }

    private function notice(array $fixture, string $title): EducationNotice
    {
        return EducationNotice::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'notice_no' => uniqid('NOT', false),
            'notice_type' => 'academic',
            'target_type' => 'class',
            'target_id' => $fixture['class_id'],
            'title' => $title,
            'content' => 'Content.',
            'priority' => 'normal',
            'status' => 'draft',
        ]);
    }
}

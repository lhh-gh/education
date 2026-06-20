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
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\NoticeReceiptRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class NoticeReceiptRepositoryTest extends AcademicTestCase
{
    public function testPublishClassTargetCreatesReceiptsOnlyForReceivableGuardians(): void
    {
        $fixture = $this->fixture();
        $notice = $this->notice($fixture, 'class', $fixture['class_id']);
        $student = $this->student($fixture, 'S001');
        $receivableGuardian = $this->guardian($fixture, 'Guardian A', '13800000001');
        $mutedGuardian = $this->guardian($fixture, 'Guardian B', '13800000002');
        $this->classStudent($fixture, (int) $student->id);
        $this->binding($fixture, (int) $student->id, (int) $receivableGuardian->id, true);
        $this->binding($fixture, (int) $student->id, (int) $mutedGuardian->id, false);

        $repository = make(NoticeReceiptRepository::class);
        $targets = $repository->buildPublishTargets($notice, $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]));
        $count = $repository->createReceipts($notice, $targets);

        self::assertSame(1, $count);
        self::assertSame((int) $receivableGuardian->id, (int) EducationNoticeReceipt::query()->first()->guardian_id);
    }

    public function testMarkReadIncrementsNoticeReadCountOnlyOnce(): void
    {
        $fixture = $this->fixture();
        $notice = $this->notice($fixture, 'campus', $fixture['campus_id']);
        $student = $this->student($fixture, 'S002');
        $guardian = $this->guardian($fixture, 'Guardian C', '13800000003');
        $receipt = EducationNoticeReceipt::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'notice_id' => $notice->id,
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relation' => 'mother',
            'guardian_name_snapshot' => 'Guardian C',
            'student_name_snapshot' => 'Student S002',
            'status' => 'unread',
        ]);

        $repository = make(NoticeReceiptRepository::class);
        $repository->markRead((int) $receipt->id, 3001);
        $repository->markRead((int) $receipt->id, 3001);

        self::assertSame(1, (int) $notice->refresh()->read_count);
        self::assertSame('read', $receipt->refresh()->status);
    }

    public function testPlatformContextCampusFiltersAdminReceiptsWithoutLocalFilters(): void
    {
        $fixture = $this->fixture();
        $notice = $this->notice($fixture, 'campus', $fixture['campus_id']);
        $student = $this->student($fixture, 'S003');
        $guardian = $this->guardian($fixture, 'Guardian D', '13800000004');
        $visible = EducationNoticeReceipt::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'notice_id' => $notice->id,
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relation' => 'father',
            'guardian_name_snapshot' => 'Guardian D',
            'student_name_snapshot' => 'Student S003',
            'status' => 'unread',
        ]);
        $otherCampus = $this->campus($fixture['tenant'], 'platform_receipt_branch');
        $hiddenStudent = EducationStudent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'student_no' => 'S004',
            'name' => 'Student S004',
            'status' => 'enabled',
        ]);
        $hiddenGuardian = $this->guardian($fixture, 'Guardian E', '13800000005');
        EducationNoticeReceipt::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $otherCampus->id,
            'notice_id' => $notice->id,
            'guardian_id' => $hiddenGuardian->id,
            'student_id' => $hiddenStudent->id,
            'relation' => 'father',
            'guardian_name_snapshot' => 'Guardian E',
            'student_name_snapshot' => 'Student S004',
            'status' => 'unread',
        ]);

        $result = make(NoticeReceiptRepository::class)->pageAdminReceipts((int) $notice->id, [], 1, 20, new EducationUserContext(
            userId: 1,
            tenantId: $fixture['tenant_id'],
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $fixture['campus_id']
        ));

        self::assertSame(1, $result['total']);
        self::assertSame((int) $visible->id, (int) $result['list'][0]['id']);
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('notice_receipt_repository');
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-NRR', 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-NRR', 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);

        return ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'course_id' => (int) $course->id, 'class_id' => (int) $class->id];
    }

    private function notice(array $fixture, string $targetType, int $targetId): EducationNotice
    {
        return EducationNotice::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'notice_no' => uniqid('NOT', false),
            'notice_type' => 'academic',
            'target_type' => $targetType,
            'target_id' => $targetId,
            'title' => 'Notice',
            'content' => 'Content.',
            'priority' => 'normal',
            'status' => 'draft',
        ]);
    }

    private function student(array $fixture, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_no' => $studentNo, 'name' => 'Student ' . $studentNo, 'status' => 'enabled']);
    }

    private function guardian(array $fixture, string $name, string $mobile): EducationGuardian
    {
        return EducationGuardian::query()->create(['tenant_id' => $fixture['tenant_id'], 'name' => $name, 'mobile' => $mobile, 'status' => 'enabled']);
    }

    private function binding(array $fixture, int $studentId, int $guardianId, bool $canReceiveNotice): EducationStudentGuardian
    {
        return EducationStudentGuardian::query()->create(['tenant_id' => $fixture['tenant_id'], 'student_id' => $studentId, 'guardian_id' => $guardianId, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => $canReceiveNotice, 'can_submit_leave' => true]);
    }

    private function classStudent(array $fixture, int $studentId): EducationClassStudent
    {
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'student_id' => $studentId, 'course_id' => $fixture['course_id'], 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);

        return EducationClassStudent::query()->create(['tenant_id' => $fixture['tenant_id'], 'campus_id' => $fixture['campus_id'], 'class_id' => $fixture['class_id'], 'course_id' => $fixture['course_id'], 'student_id' => $studentId, 'account_id' => $account->id, 'student_name_snapshot' => 'Student', 'student_no_snapshot' => 'S', 'status' => 'active']);
    }
}

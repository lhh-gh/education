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
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Service\Education\Academic\NoticeService;

/**
 * @internal
 * @coversNothing
 */
final class NoticeServiceTest extends AcademicTestCase
{
    public function testEmptyTargetPublishReturnsConflict(): void
    {
        $fixture = $this->fixture('notice_service_empty');
        $notice = make(NoticeService::class)->create($this->payload($fixture), $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        try {
            make(NoticeService::class)->publish((int) $notice->id, [], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
            self::fail('Expected empty notice target to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    public function testPublishClassTargetCreatesReceiptsAndUpdatesNotice(): void
    {
        $fixture = $this->fixture('notice_service_publish');
        $student = $this->student($fixture, 'S101');
        $guardian = $this->guardian($fixture, 'Guardian A', '13810000001');
        $this->classStudent($fixture, (int) $student->id);
        $this->binding($fixture, (int) $student->id, (int) $guardian->id, true);
        $service = make(NoticeService::class);
        $notice = $service->create($this->payload($fixture), $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        $result = $service->publish((int) $notice->id, ['published_at' => '2026-06-12 09:00:00'], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame(1, $result['receipt_count']);
        self::assertSame('published', $notice->refresh()->status);
        self::assertSame(1, (int) $notice->receipt_count);
        self::assertSame(1, EducationNoticeReceipt::query()->where('notice_id', $notice->id)->count());
    }

    public function testWithdrawPublishedNoticeStoresReason(): void
    {
        $fixture = $this->fixture('notice_service_withdraw');
        $student = $this->student($fixture, 'S102');
        $guardian = $this->guardian($fixture, 'Guardian B', '13810000002');
        $this->classStudent($fixture, (int) $student->id);
        $this->binding($fixture, (int) $student->id, (int) $guardian->id, true);
        $service = make(NoticeService::class);
        $notice = $service->create($this->payload($fixture), $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);
        $service->publish((int) $notice->id, [], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        $withdrawn = $service->withdraw((int) $notice->id, ['withdraw_reason' => 'Wrong class target'], $this->context($fixture['tenant_id'], campusIds: [$fixture['campus_id']]), 901);

        self::assertSame('withdrawn', $withdrawn->status);
        self::assertSame('Wrong class target', $withdrawn->withdraw_reason);
    }

    private function fixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => mb_strtoupper($code), 'name' => 'Art', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-' . mb_strtoupper($code), 'name' => 'Class', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);

        return ['tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'course_id' => (int) $course->id, 'class_id' => (int) $class->id];
    }

    private function payload(array $fixture): array
    {
        return [
            'campus_id' => $fixture['campus_id'],
            'notice_type' => 'academic',
            'target_type' => 'class',
            'target_id' => $fixture['class_id'],
            'title' => 'Class reminder',
            'content' => 'Bring tools.',
            'priority' => 'important',
        ];
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

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

namespace HyperfTests\Feature\Education\Academic;

use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Context\Context;

/**
 * @internal
 * @coversNothing
 */
final class TeacherMobileLessonApiTest extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::destroy(MobileContextService::CONTEXT_KEY);
    }

    protected function tearDown(): void
    {
        Context::destroy(MobileContextService::CONTEXT_KEY);
        parent::tearDown();
    }

    public function testTodayLessonsContract(): void
    {
        $fixture = $this->fixture();
        $lesson = $this->lesson($fixture, '2026-06-12 09:00:00');

        $result = $this->get('/mobile/education/academic/teacher/lessons/today', [
            'campus_id' => $fixture['campus_id'],
            'date' => '2026-06-12',
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame('2026-06-12', $result['data']['date']);
        self::assertSame((int) $lesson->id, $result['data']['list'][0]['id']);
        self::assertArrayHasKey('pending_leave_count', $result['data']['list'][0]);
    }

    public function testLessonDetailWrongTeacherReturns404(): void
    {
        $fixture = $this->fixture();
        $lesson = $this->lesson($fixture, '2026-06-12 09:00:00', $fixture['teacher_id'] + 99);

        $result = $this->get('/mobile/education/academic/teacher/lessons/' . $lesson->id, [
            'campus_id' => $fixture['campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        self::assertSame('lesson not found in current teacher context', $result['message']);
    }

    public function testInvalidDateReturns422(): void
    {
        $fixture = $this->fixture();

        $result = $this->get('/mobile/education/academic/teacher/lessons/today', [
            'campus_id' => $fixture['campus_id'],
            'date' => '2026/06/12',
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $result['code']);
        self::assertSame('date must use Y-m-d', $result['message']);
    }

    public function testLessonDetailReturnsStudents(): void
    {
        $fixture = $this->fixture();
        $lesson = $this->lesson($fixture, '2026-06-12 09:00:00');
        $student = $this->lessonStudent($fixture, (int) $lesson->id);

        $result = $this->get('/mobile/education/academic/teacher/lessons/' . $lesson->id, [
            'campus_id' => $fixture['campus_id'],
        ], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame((int) $student->id, $result['data']['lesson_students'][0]['lesson_student_id']);
        self::assertTrue($result['data']['can_submit_attendance']);
    }

    private function fixture(): array
    {
        $tenant = $this->tenant('teacher_mobile_lesson_api');
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'tenant:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => 'teacher',
            'display_name' => 'Teacher Mobile',
            'status' => 'enabled',
            'current_campus_id' => $campus->id,
        ]);
        EducationUserCampusScope::query()->create([
            'tenant_id' => $tenant->id,
            'user_profile_id' => $profile->id,
            'user_id' => $this->user->id,
            'campus_id' => $campus->id,
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $profile->id,
            'teacher_no' => 'T-API',
            'name' => 'Teacher Mobile',
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'ART-001',
            'name' => 'Art Basics',
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'C-001',
            'name' => 'Sunday Art',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);

        return [
            'tenant' => $tenant,
            'campus' => $campus,
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'teacher_id' => (int) $teacher->id,
            'class_id' => (int) $class->id,
            'course_id' => (int) $course->id,
        ];
    }

    private function lesson(array $fixture, string $startAt, ?int $teacherId = null): EducationLesson
    {
        return EducationLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_no' => uniqid('L', false),
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'teacher_id' => $teacherId ?? $fixture['teacher_id'],
            'classroom_id' => 201,
            'title' => 'Art Basics Lesson',
            'start_at' => $startAt,
            'end_at' => date('Y-m-d H:i:s', strtotime($startAt . ' +1 hour')),
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Sunday Art',
            'course_name_snapshot' => 'Art Basics',
            'teacher_name_snapshot' => 'Teacher Mobile',
            'classroom_name_snapshot' => 'Room 1',
        ]);
    }

    private function lessonStudent(array $fixture, int $lessonId): EducationLessonStudent
    {
        return EducationLessonStudent::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lesson_id' => $lessonId,
            'class_id' => $fixture['class_id'],
            'course_id' => $fixture['course_id'],
            'student_id' => 1001,
            'account_id' => 2001,
            'student_name_snapshot' => 'Student Zhang',
            'student_no_snapshot' => 'S001',
            'lesson_units' => '1.00',
            'status' => 'planned',
        ]);
    }

    private function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, [
            'X-Client-Type' => 'wechat_miniprogram',
        ]);
    }
}

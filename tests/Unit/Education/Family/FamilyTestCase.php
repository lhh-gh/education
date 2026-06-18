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

namespace HyperfTests\Unit\Education\Family;

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Family\EducationFamilyMessage;
use App\Model\Education\Family\EducationFamilyReadReceipt;
use App\Model\Education\Family\EducationFamilyServiceAttachment;
use App\Model\Education\Family\EducationGrowthRecord;
use App\Model\Education\Family\EducationHomeworkAssignment;
use App\Model\Education\Family\EducationHomeworkReview;
use App\Model\Education\Family\EducationHomeworkSubmission;
use App\Model\Education\Family\EducationHomeworkTarget;
use App\Model\Education\Family\EducationLearningReport;
use App\Model\Education\Family\EducationLearningReportItem;
use App\Model\Education\Family\EducationLessonComment;
use App\Model\Education\Family\EducationLessonCommentTagRelation;
use App\Model\Education\Family\EducationLessonCommentTemplate;
use App\Model\Education\Family\EducationServiceQualityMetric;
use App\Model\Education\Family\EducationStudentPerformanceTag;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Academic\AcademicTestCase;

abstract class FamilyTestCase extends AcademicTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureFamilyTables();
        $this->cleanFamilyData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function familyFixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $teacherUserId = random_int(10000, 49999);
        $guardianUserId = random_int(50000, 99999);
        $teacherProfile = EducationUserProfile::query()->create([
            'profile_key' => 'teacher-' . $teacherUserId,
            'tenant_id' => $tenant->id,
            'user_id' => $teacherUserId,
            'role_code' => EducationRoleCode::Teacher->value,
            'display_name' => 'Teacher',
            'mobile' => '139' . random_int(10000000, 99999999),
            'status' => 'enabled',
        ]);
        $guardianProfile = EducationUserProfile::query()->create([
            'profile_key' => 'guardian-' . $guardianUserId,
            'tenant_id' => $tenant->id,
            'user_id' => $guardianUserId,
            'role_code' => EducationRoleCode::Guardian->value,
            'display_name' => 'Guardian',
            'mobile' => '138' . random_int(10000000, 99999999),
            'status' => 'enabled',
        ]);
        $teacher = EducationTeacher::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'user_profile_id' => $teacherProfile->id,
            'teacher_no' => 'T-' . mb_strtoupper($code),
            'name' => 'Teacher',
            'mobile' => $teacherProfile->mobile,
            'status' => 'enabled',
        ]);
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian',
            'mobile' => $guardianProfile->mobile,
            'status' => 'enabled',
        ]);
        $course = EducationCourse::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'code' => 'C-' . mb_strtoupper($code),
            'name' => 'Art',
            'status' => 'enabled',
        ]);
        $class = EducationClass::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => $course->id,
            'code' => 'CLS-' . mb_strtoupper($code),
            'name' => 'Class',
            'class_type' => 'group',
            'lesson_units' => '1.00',
            'status' => 'enabled',
        ]);
        $student = $this->student($tenant->id, $campus->id, 'S-' . mb_strtoupper($code));
        $otherStudent = $this->student($tenant->id, $campus->id, 'S-OTHER-' . mb_strtoupper($code));
        EducationStudentGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'relation' => 'mother',
            'is_primary' => true,
            'can_receive_notice' => true,
            'can_submit_leave' => true,
        ]);
        $lesson = EducationLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_no' => uniqid('L', false),
            'class_id' => $class->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'classroom_id' => 201,
            'title' => 'Art Lesson',
            'start_at' => '2026-06-12 09:00:00',
            'end_at' => '2026-06-12 10:00:00',
            'duration_minutes' => 60,
            'lesson_units' => '1.00',
            'student_count' => 1,
            'status' => 'scheduled',
            'source_type' => 'manual',
            'class_name_snapshot' => 'Class',
            'course_name_snapshot' => 'Art',
            'teacher_name_snapshot' => 'Teacher',
        ]);
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'purchased_units' => '10.00',
            'bonus_units' => '0.00',
            'consumed_units' => '0.00',
            'adjusted_units' => '0.00',
            'refunded_units' => '0.00',
            'frozen_units' => '0.00',
            'available_units' => '10.00',
            'status' => 'active',
        ]);
        EducationLessonStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => $lesson->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'account_id' => $account->id,
            'student_name_snapshot' => 'Student',
            'student_no_snapshot' => 'S001',
            'lesson_units' => '1.00',
            'status' => 'planned',
        ]);

        return [
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'course_id' => (int) $course->id,
            'class_id' => (int) $class->id,
            'lesson_id' => (int) $lesson->id,
            'teacher_id' => (int) $teacher->id,
            'teacher_user_id' => $teacherUserId,
            'guardian_id' => (int) $guardian->id,
            'guardian_user_id' => $guardianUserId,
            'student_id' => (int) $student->id,
            'other_student_id' => (int) $otherStudent->id,
        ];
    }

    private function ensureFamilyTables(): void
    {
        if (Schema::hasTable('edu_lesson_comments')) {
            return;
        }

        $migration = $this->familyMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanFamilyData(): void
    {
        EducationFamilyServiceAttachment::query()->forceDelete();
        EducationServiceQualityMetric::query()->whereRaw('1 = 1')->delete();
        EducationFamilyReadReceipt::query()->whereRaw('1 = 1')->delete();
        EducationFamilyMessage::query()->forceDelete();
        EducationLearningReportItem::query()->whereRaw('1 = 1')->delete();
        EducationLearningReport::query()->forceDelete();
        EducationGrowthRecord::query()->forceDelete();
        EducationHomeworkReview::query()->forceDelete();
        EducationHomeworkSubmission::query()->forceDelete();
        EducationHomeworkTarget::query()->whereRaw('1 = 1')->delete();
        EducationHomeworkAssignment::query()->forceDelete();
        EducationLessonCommentTagRelation::query()->whereRaw('1 = 1')->delete();
        EducationStudentPerformanceTag::query()->forceDelete();
        EducationLessonCommentTemplate::query()->forceDelete();
        EducationLessonComment::query()->forceDelete();
    }

    private function student(int $tenantId, int $campusId, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_no' => $studentNo,
            'name' => 'Student',
            'status' => 'enabled',
        ]);
    }

    private function familyMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_070000_create_v7_family_service_tables.php';
    }
}

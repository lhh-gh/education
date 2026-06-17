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

namespace HyperfTests\Feature\Education\Family;

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
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Academic\ProfileRecordAdminCase;

abstract class FamilyApiCase extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureFamilyTables();
        $this->cleanFamilyData();
    }

    protected function tearDown(): void
    {
        $this->cleanFamilyData();
        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    protected function familyFixture(string $code, string $roleCode): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $profile = EducationUserProfile::query()->create([
            'profile_key' => 'family:' . $tenant->id . ':' . $this->user->id,
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role_code' => $roleCode,
            'display_name' => 'Family User',
            'mobile' => '138' . random_int(10000000, 99999999),
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
            'teacher_no' => 'T-' . mb_strtoupper($code),
            'name' => 'Teacher',
            'mobile' => $profile->mobile,
            'status' => 'enabled',
        ]);
        $guardian = EducationGuardian::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guardian',
            'mobile' => $profile->mobile,
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
        $student = $this->student($tenant, $campus, 'S-' . mb_strtoupper($code));
        $otherStudent = $this->student($tenant, $campus, 'S-OTHER-' . mb_strtoupper($code));
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
            'tenant' => $tenant,
            'campus' => $campus,
            'tenant_id' => (int) $tenant->id,
            'campus_id' => (int) $campus->id,
            'course_id' => (int) $course->id,
            'class_id' => (int) $class->id,
            'lesson_id' => (int) $lesson->id,
            'teacher_id' => (int) $teacher->id,
            'guardian_id' => (int) $guardian->id,
            'student_id' => (int) $student->id,
            'other_student_id' => (int) $otherStudent->id,
        ];
    }

    protected function mobileHeaders(EducationTenant $tenant): array
    {
        return $this->tenantHeaders($tenant, ['X-Client-Type' => 'wechat_miniprogram']);
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

    private function student(EducationTenant $tenant, EducationCampus $campus, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
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

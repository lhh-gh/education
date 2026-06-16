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

use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationClassStudent;
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;

trait NoticeAdminApiFixture
{
    protected function noticeFixture(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main');
        $this->createTenantProfile($tenant, 'tenant_admin', $campus);
        $course = EducationCourse::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'code' => 'ART-' . mb_strtoupper($code), 'name' => 'Art Basics', 'status' => 'enabled']);
        $class = EducationClass::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'course_id' => $course->id, 'code' => 'CLS-' . mb_strtoupper($code), 'name' => 'Sunday Art', 'class_type' => 'group', 'lesson_units' => '1.00', 'status' => 'enabled']);
        $student = EducationStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_no' => 'S-' . mb_strtoupper($code), 'name' => 'Student Zhang', 'status' => 'enabled']);
        $guardian = EducationGuardian::query()->create(['tenant_id' => $tenant->id, 'name' => 'Guardian Zhang', 'mobile' => '136' . random_int(10000000, 99999999), 'status' => 'enabled']);
        EducationStudentGuardian::query()->create(['tenant_id' => $tenant->id, 'student_id' => $student->id, 'guardian_id' => $guardian->id, 'relation' => 'mother', 'is_primary' => true, 'can_receive_notice' => true, 'can_submit_leave' => true]);
        $account = EducationStudentCourseAccount::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'student_id' => $student->id, 'course_id' => $course->id, 'purchased_units' => '10.00', 'bonus_units' => '0.00', 'consumed_units' => '0.00', 'adjusted_units' => '0.00', 'refunded_units' => '0.00', 'frozen_units' => '0.00', 'available_units' => '10.00', 'status' => 'active']);
        EducationClassStudent::query()->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id, 'class_id' => $class->id, 'course_id' => $course->id, 'student_id' => $student->id, 'account_id' => $account->id, 'student_name_snapshot' => 'Student Zhang', 'student_no_snapshot' => 'S001', 'status' => 'active']);

        return ['tenant' => $tenant, 'tenant_id' => (int) $tenant->id, 'campus_id' => (int) $campus->id, 'class_id' => (int) $class->id, 'student_id' => (int) $student->id, 'guardian_id' => (int) $guardian->id];
    }

    protected function noticePayload(array $fixture): array
    {
        return [
            'campus_id' => $fixture['campus_id'],
            'notice_type' => 'academic',
            'target_type' => 'class',
            'target_id' => $fixture['class_id'],
            'title' => 'Class reminder',
            'content' => 'Bring tools tomorrow.',
            'priority' => 'important',
        ];
    }
}

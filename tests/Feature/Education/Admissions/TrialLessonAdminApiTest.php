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

namespace HyperfTests\Feature\Education\Admissions;

use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLeadStudent;

/**
 * @internal
 * @coversNothing
 */
final class TrialLessonAdminApiTest extends AdmissionsApiCase
{
    public function testTrialLessonConflictResponseMatchesCatalog(): void
    {
        $this->grantPermissions('education:admissions:trial:create');
        $tenant = $this->tenant('adm_api_trial');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, campus: $campus);
        $lead = $this->admissionsLead($tenant, $campus);
        $student = EducationLeadStudent::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->first();

        $first = $this->post('/admin/education/admissions/trial-lessons', [
            'lead_id' => $lead->id,
            'lead_student_id' => $student->id,
            'course_id' => 501,
            'teacher_id' => 701,
            'classroom_id' => 801,
            'start_time' => '2026-06-13 09:00:00',
            'end_time' => '2026-06-13 10:00:00',
        ], $this->tenantHeaders($tenant));
        $conflict = $this->post('/admin/education/admissions/trial-lessons', [
            'lead_id' => $lead->id,
            'lead_student_id' => $student->id,
            'course_id' => 501,
            'teacher_id' => 701,
            'classroom_id' => 802,
            'start_time' => '2026-06-13 09:30:00',
            'end_time' => '2026-06-13 10:30:00',
        ], $this->tenantHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $first['code']);
        self::assertSame(ResultCode::CONFLICT->value, $conflict['code']);
        self::assertSame($first['data']['id'], $conflict['data']['conflict_lesson_id']);
    }
}

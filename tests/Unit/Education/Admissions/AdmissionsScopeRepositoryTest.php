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

namespace HyperfTests\Unit\Education\Admissions;

use App\Model\Education\Admissions\EducationAdmissionMetricDaily;
use App\Model\Education\Admissions\EducationAdmissionTask;
use App\Model\Education\Admissions\EducationLeadSource;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Admissions\AdmissionMetricRepository;
use App\Repository\Education\Admissions\AdmissionTaskRepository;
use App\Repository\Education\Admissions\LeadSourceRepository;
use App\Repository\Education\Admissions\TrialLessonRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class AdmissionsScopeRepositoryTest extends AdmissionsTestCase
{
    public function testPlatformCurrentCampusFiltersAdmissionTasksWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('adm_scope_task');
        $visible = EducationAdmissionTask::query()->create($this->taskData($tenantId, $campusId, 101));
        EducationAdmissionTask::query()->create($this->taskData($tenantId, $otherCampusId, 102));

        $page = make(AdmissionTaskRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersTrialLessonsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('adm_scope_trial');
        $visible = EducationTrialLesson::query()->create($this->trialData($tenantId, $campusId, 201));
        $other = EducationTrialLesson::query()->create($this->trialData($tenantId, $otherCampusId, 202));
        $context = $this->platformContext($tenantId, $campusId);

        $page = make(TrialLessonRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNotNull(make(TrialLessonRepository::class)->findScoped((int) $visible->id, $context));
        self::assertNull(make(TrialLessonRepository::class)->findScoped((int) $other->id, $context));
    }

    public function testPlatformCurrentCampusFiltersLeadSourcesWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('adm_scope_source');
        $visible = EducationLeadSource::query()->create($this->sourceData($tenantId, $campusId, 'web_a'));
        EducationLeadSource::query()->create($this->sourceData($tenantId, $otherCampusId, 'web_b'));

        $page = make(LeadSourceRepository::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersAdmissionMetricSummaryWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('adm_scope_metric');
        EducationAdmissionMetricDaily::query()->create($this->metricData($tenantId, $campusId, 3));
        EducationAdmissionMetricDaily::query()->create($this->metricData($tenantId, $otherCampusId, 7));

        $summary = make(AdmissionMetricRepository::class)->summary([], $this->platformContext($tenantId, $campusId));

        self::assertSame(3, $summary['new_leads_count']);
        self::assertSame(3, $summary['follow_count']);
        self::assertSame(3, $summary['trial_count']);
        self::assertSame(3, $summary['converted_count']);
    }

    private function tenantCampusPair(string $code): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant, 'main_' . $code);
        $otherCampus = $this->campus($tenant, 'branch_' . $code);

        return [(int) $tenant->id, (int) $campus->id, (int) $otherCampus->id];
    }

    private function platformContext(int $tenantId, int $campusId): EducationUserContext
    {
        return new EducationUserContext(
            userId: 1,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $campusId
        );
    }

    private function taskData(int $tenantId, int $campusId, int $leadId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lead_id' => $leadId,
            'task_type' => 'follow',
            'title' => 'Follow lead',
            'assignee_user_id' => 9001,
            'status' => 'pending',
            'due_at' => '2026-06-20 10:00:00',
        ];
    }

    private function trialData(int $tenantId, int $campusId, int $leadId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lead_id' => $leadId,
            'lead_student_id' => $leadId,
            'course_id' => 501,
            'teacher_id' => 701,
            'classroom_id' => 801,
            'start_time' => '2026-06-20 10:00:00',
            'end_time' => '2026-06-20 11:00:00',
            'status' => 'scheduled',
        ];
    }

    private function sourceData(int $tenantId, int $campusId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'code' => $code,
            'name' => 'Web',
            'channel_type' => 'online',
            'status' => 'enabled',
            'sort_order' => 1,
        ];
    }

    private function metricData(int $tenantId, int $campusId, int $count): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => '2026-06-20',
            'new_leads_count' => $count,
            'follow_count' => $count,
            'trial_count' => $count,
            'trial_attended_count' => $count,
            'converted_count' => $count,
        ];
    }
}

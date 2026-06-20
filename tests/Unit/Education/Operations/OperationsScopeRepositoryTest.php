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

namespace HyperfTests\Unit\Education\Operations;

use App\Model\Education\Operations\EducationDailyOperationMetric;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Model\Education\Operations\EducationLessonConsumptionReview;
use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Education\Operations\EducationMakeupRecord;
use App\Model\Education\Operations\EducationRenewalAlert;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Operations\ConsumptionReviewRepository;
use App\Repository\Education\Operations\DailyOperationMetricRepository;
use App\Repository\Education\Operations\LessonChangeRepository;
use App\Repository\Education\Operations\MakeupEntitlementRepository;
use App\Repository\Education\Operations\MakeupRecordRepository;
use App\Repository\Education\Operations\RenewalAlertRepository;
use App\Repository\Education\Operations\TeacherWorkloadRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class OperationsScopeRepositoryTest extends OperationsTestCase
{
    public function testPlatformCurrentCampusFiltersRenewalAlertsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('ops_scope_renewal');
        $visible = $this->renewalAlert($tenantId, $campusId, 101);
        $this->renewalAlert($tenantId, $otherCampusId, 102);

        $page = make(RenewalAlertRepository::class)->pageOpen([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersConsumptionReviewsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('ops_scope_review');
        $visible = EducationLessonConsumptionReview::query()->create($this->reviewData($tenantId, $campusId, 201));
        EducationLessonConsumptionReview::query()->create($this->reviewData($tenantId, $otherCampusId, 202));

        $page = make(ConsumptionReviewRepository::class)->pagePending([
            'page' => 1,
            'pageSize' => 20,
        ], $this->platformContext($tenantId, $campusId));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersMakeupPagesWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('ops_scope_makeup');
        $visibleEntitlement = EducationMakeupEntitlement::query()->create($this->entitlementData($tenantId, $campusId, 301));
        $otherEntitlement = EducationMakeupEntitlement::query()->create($this->entitlementData($tenantId, $otherCampusId, 302));
        $visibleRecord = EducationMakeupRecord::query()->create($this->recordData($tenantId, $campusId, (int) $visibleEntitlement->id, 401));
        EducationMakeupRecord::query()->create($this->recordData($tenantId, $otherCampusId, (int) $otherEntitlement->id, 402));
        $context = $this->platformContext($tenantId, $campusId);

        $entitlementPage = make(MakeupEntitlementRepository::class)->pageAvailable([
            'page' => 1,
            'pageSize' => 20,
        ], $context);
        $recordPage = make(MakeupRecordRepository::class)->pageByStudent([
            'page' => 1,
            'pageSize' => 20,
        ], $context);

        self::assertSame(1, $entitlementPage['total']);
        self::assertSame((int) $visibleEntitlement->id, (int) $entitlementPage['list'][0]['id']);
        self::assertSame(1, $recordPage['total']);
        self::assertSame((int) $visibleRecord->id, (int) $recordPage['list'][0]['id']);
    }

    public function testPlatformCurrentCampusFiltersLessonChangeAndWorkloadPagesWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('ops_scope_workload');
        $visibleChange = EducationLessonChangeRequest::query()->create($this->changeRequestData($tenantId, $campusId, 501));
        EducationLessonChangeRequest::query()->create($this->changeRequestData($tenantId, $otherCampusId, 502));
        $visibleWorkload = EducationTeacherWorkloadRecord::query()->create($this->workloadData($tenantId, $campusId, 601));
        EducationTeacherWorkloadRecord::query()->create($this->workloadData($tenantId, $otherCampusId, 602));
        $context = $this->platformContext($tenantId, $campusId);

        $changePage = make(LessonChangeRepository::class)->pageByCampusScope([
            'page' => 1,
            'pageSize' => 20,
        ], $context);
        $workloadPage = make(TeacherWorkloadRepository::class)->pageReport([], $context);

        self::assertSame(1, $changePage['total']);
        self::assertSame((int) $visibleChange->id, (int) $changePage['list'][0]['id']);
        self::assertCount(1, $workloadPage);
        self::assertSame((int) $visibleWorkload->id, (int) $workloadPage[0]['id']);
    }

    public function testPlatformCurrentCampusFiltersDailyOperationMetricsWithoutLocalCampusFilter(): void
    {
        [$tenantId, $campusId, $otherCampusId] = $this->tenantCampusPair('ops_scope_metric');
        $visible = EducationDailyOperationMetric::query()->create($this->metricData($tenantId, $campusId, '2026-06-20'));
        EducationDailyOperationMetric::query()->create($this->metricData($tenantId, $otherCampusId, '2026-06-20'));

        $rows = make(DailyOperationMetricRepository::class)->trend($this->platformContext($tenantId, $campusId));

        self::assertCount(1, $rows);
        self::assertSame((int) $visible->id, (int) $rows[0]['id']);
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

    private function renewalAlert(int $tenantId, int $campusId, int $studentId): EducationRenewalAlert
    {
        return EducationRenewalAlert::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => 1,
            'student_course_account_id' => $studentId,
            'alert_type' => 'low_balance',
            'alert_level' => 'urgent',
            'status' => 'open',
            'trigger_value' => '1.00',
            'threshold_value' => '2.00',
            'due_date' => '2026-06-20',
        ]);
    }

    private function reviewData(int $tenantId, int $campusId, int $lessonId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_id' => $lessonId,
            'status' => 'pending',
            'submitted_by' => 9001,
            'submitted_at' => '2026-06-20 10:00:00',
        ];
    }

    private function entitlementData(int $tenantId, int $campusId, int $studentId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'course_id' => 1,
            'source_lesson_id' => $studentId,
            'source_leave_request_id' => $studentId,
            'status' => 'available',
        ];
    }

    private function recordData(int $tenantId, int $campusId, int $entitlementId, int $studentId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'makeup_entitlement_id' => $entitlementId,
            'student_id' => $studentId,
            'makeup_lesson_id' => $studentId,
            'status' => 'arranged',
            'arranged_by' => 9001,
            'arranged_at' => '2026-06-20 10:00:00',
        ];
    }

    private function changeRequestData(int $tenantId, int $campusId, int $lessonId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'lesson_id' => $lessonId,
            'change_type' => 'reschedule',
            'status' => 'pending',
            'old_values_json' => ['start_time' => '2026-06-20 10:00:00'],
            'new_values_json' => ['start_time' => '2026-06-20 11:00:00'],
            'reason' => 'teacher training',
            'requested_by' => 9001,
        ];
    }

    private function workloadData(int $tenantId, int $campusId, int $lessonId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'teacher_id' => 2001,
            'lesson_id' => $lessonId,
            'workload_type' => 'main',
            'lesson_type' => 'normal',
            'credits' => '1.00',
            'student_count' => 1,
            'present_count' => 1,
            'recorded_at' => '2026-06-20 10:00:00',
        ];
    }

    private function metricData(int $tenantId, int $campusId, string $date): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $date,
            'lessons_count' => 1,
            'consumed_credits' => '1.00',
            'renewal_alert_count' => 1,
            'pending_review_count' => 1,
        ];
    }
}

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

use App\Exception\BusinessException;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Family\EducationHomeworkAssignment;
use App\Model\Education\Family\EducationLearningReport;
use App\Model\Education\Family\EducationLearningReportItem;
use App\Model\Education\Family\EducationLessonCommentTemplate;
use App\Model\Education\Family\EducationStudentPerformanceTag;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Family\HomeworkRepository;
use App\Repository\Education\Family\LearningReportRepository;
use App\Repository\Education\Family\LessonCommentRepository;
use App\Service\Education\Family\LearningReportService;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class FamilyScopeRepositoryTest extends FamilyTestCase
{
    public function testHomeworkAssignmentsUseCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('family_scope_homework');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $repository = make(HomeworkRepository::class);

        $visible = EducationHomeworkAssignment::query()->create($this->homeworkAssignmentData((int) $tenant->id, (int) $visibleCampus->id, 'Visible homework'));
        EducationHomeworkAssignment::query()->create($this->homeworkAssignmentData((int) $tenant->id, (int) $hiddenCampus->id, 'Hidden homework'));

        $page = $repository->pageAssignments([], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testLearningReportsUseCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('family_scope_report');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $student = $this->createStudent((int) $tenant->id, (int) $visibleCampus->id, 'S-FAMILY-SCOPE-REPORT');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $repository = make(LearningReportRepository::class);

        $visible = EducationLearningReport::query()->create($this->learningReportData((int) $tenant->id, (int) $visibleCampus->id, (int) $student->id, 'Visible report'));
        $hidden = EducationLearningReport::query()->create($this->learningReportData((int) $tenant->id, (int) $hiddenCampus->id, (int) $student->id, 'Hidden report'));

        $page = $repository->visibleForGuardian((int) $student->id, [], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertSame((int) $visible->id, (int) $repository->find((int) $visible->id, $context)?->id);
        self::assertNull($repository->find((int) $hidden->id, $context));
    }

    public function testLearningReportPublishRejectsHiddenCampusRecord(): void
    {
        $tenant = $this->tenant('family_scope_report_publish');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $student = $this->createStudent((int) $tenant->id, (int) $visibleCampus->id, 'S-FAMILY-SCOPE-PUBLISH');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $report = EducationLearningReport::query()->create($this->learningReportData((int) $tenant->id, (int) $hiddenCampus->id, (int) $student->id, 'Hidden report'));
        EducationLearningReportItem::query()->create($this->learningReportItemData((int) $tenant->id, (int) $hiddenCampus->id, (int) $report->id));

        $this->expectException(BusinessException::class);

        make(LearningReportService::class)->publish((int) $report->id, $context);
    }

    public function testLessonCommentConfigUsesCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('family_scope_comment_config');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $repository = make(LessonCommentRepository::class);

        $visibleTemplate = EducationLessonCommentTemplate::query()->create($this->templateData((int) $tenant->id, (int) $visibleCampus->id, 'TPL-FAMILY-SCOPE-001'));
        EducationLessonCommentTemplate::query()->create($this->templateData((int) $tenant->id, (int) $hiddenCampus->id, 'TPL-FAMILY-SCOPE-002'));
        $visibleTag = EducationStudentPerformanceTag::query()->create($this->tagData((int) $tenant->id, (int) $visibleCampus->id, 'TAG-FAMILY-SCOPE-001'));
        EducationStudentPerformanceTag::query()->create($this->tagData((int) $tenant->id, (int) $hiddenCampus->id, 'TAG-FAMILY-SCOPE-002'));

        $templates = $repository->pageTemplates([], $context);
        $tags = $repository->pageTags([], $context);

        self::assertSame(1, $templates['total']);
        self::assertSame((int) $visibleTemplate->id, (int) $templates['list'][0]['id']);
        self::assertSame(1, $tags['total']);
        self::assertSame((int) $visibleTag->id, (int) $tags['list'][0]['id']);
    }

    private function platformContext(int $tenantId, int $currentCampusId): EducationUserContext
    {
        return new EducationUserContext(
            userId: 9400,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $currentCampusId
        );
    }

    private function createStudent(int $tenantId, int $campusId, string $studentNo): EducationStudent
    {
        return EducationStudent::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_no' => $studentNo,
            'name' => 'Family Scope Student',
            'status' => 'enabled',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function homeworkAssignmentData(int $tenantId, int $campusId, string $title): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'title' => $title,
            'content' => $title,
            'status' => 'published',
            'publish_at' => '2026-06-20 10:00:00',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function learningReportData(int $tenantId, int $campusId, int $studentId, string $title): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'student_id' => $studentId,
            'report_title' => $title,
            'report_period' => '2026-06',
            'status' => 'published',
            'published_at' => '2026-06-20 10:00:00',
            'summary' => $title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function learningReportItemData(int $tenantId, int $campusId, int $reportId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'learning_report_id' => $reportId,
            'item_type' => 'summary',
            'title' => 'Summary',
            'content' => 'Visible text',
            'sort_order' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateData(int $tenantId, int $campusId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'template_code' => $code,
            'template_name' => $code,
            'content' => 'Well done',
            'status' => 'enabled',
            'sort_order' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tagData(int $tenantId, int $campusId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'tag_code' => $code,
            'tag_name' => $code,
            'tag_type' => 'positive',
            'status' => 'enabled',
            'sort_order' => 1,
        ];
    }
}

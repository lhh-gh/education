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

namespace HyperfTests\Unit\Education\Content;

use App\Model\Education\Content\EducationStageAchievementShowcase;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\ShowcaseService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class ShowcaseServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_showcase_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-showcase');
        $visible = EducationStageAchievementShowcase::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'stage_goal_id' => 3301,
            'title' => 'Visible Showcase',
            'summary' => 'Visible current campus showcase',
            'status' => 'published',
            'published_at' => '2026-06-10 10:00:00',
        ]);
        EducationStageAchievementShowcase::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'student_id' => 1202,
            'stage_goal_id' => 3302,
            'title' => 'Hidden Showcase',
            'summary' => 'Hidden other campus showcase',
            'status' => 'published',
            'published_at' => '2026-06-10 10:00:00',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9904);

        $page = make(ShowcaseService::class)->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testGuardianSeesPublishedShowcaseForBoundStudentOnly(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_showcase');
        $service = make(ShowcaseService::class);
        $published = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'stage_goal_id' => 3301,
            'title' => 'Stage 1 Achievement',
            'summary' => 'Published showcase',
            'items' => [['item_type' => 'text', 'title' => 'Highlight', 'content' => 'Great progress']],
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9904);
        $service->publish($context, $published['showcase_id']);
        $withdrawn = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'title' => 'Withdrawn Achievement',
            'summary' => 'Hidden showcase',
        ]);
        $service->publish($context, $withdrawn['showcase_id']);
        $service->withdraw($context, $withdrawn['showcase_id']);

        $page = $service->pageGuardianShowcases((int) $tenant->id, 1201, [1201]);

        self::assertSame(1, $page['total']);
        self::assertSame('Stage 1 Achievement', $page['list'][0]['title']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('student is not bound to current guardian');

        $service->pageGuardianShowcases((int) $tenant->id, 1202, [1201]);
    }

    public function testSaveUsesCurrentCampusScopeForExistingShowcase(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_showcase_save_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-showcase-save');
        $created = make(ShowcaseService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'student_id' => 1202,
            'stage_goal_id' => 3302,
            'title' => 'Hidden Save Showcase',
            'summary' => 'Hidden other campus showcase',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9904);

        $this->expectException(ModelNotFoundException::class);

        make(ShowcaseService::class)->save([
            'id' => $created['showcase_id'],
            'student_id' => 1202,
            'stage_goal_id' => 3302,
            'title' => 'Hidden Save Showcase Updated',
            'summary' => 'Hidden other campus showcase updated',
        ], $context);
    }

    public function testPublishUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_showcase_publish_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-showcase-publish');
        $hidden = EducationStageAchievementShowcase::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'student_id' => 1202,
            'stage_goal_id' => 3302,
            'title' => 'Hidden Showcase',
            'summary' => 'Hidden other campus showcase',
            'status' => 'draft',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9904);

        $this->expectException(ModelNotFoundException::class);

        make(ShowcaseService::class)->publish($context, (int) $hidden->id);
    }

    public function testWithdrawUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_showcase_withdraw_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-showcase-withdraw');
        $hidden = EducationStageAchievementShowcase::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'student_id' => 1202,
            'stage_goal_id' => 3302,
            'title' => 'Hidden Showcase',
            'summary' => 'Hidden other campus showcase',
            'status' => 'published',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9904);

        $this->expectException(ModelNotFoundException::class);

        make(ShowcaseService::class)->withdraw($context, (int) $hidden->id);
    }
}

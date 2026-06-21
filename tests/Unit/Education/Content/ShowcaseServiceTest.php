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
        $service->publish((int) $tenant->id, $published['showcase_id']);
        $withdrawn = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 1201,
            'title' => 'Withdrawn Achievement',
            'summary' => 'Hidden showcase',
        ]);
        $service->publish((int) $tenant->id, $withdrawn['showcase_id']);
        $service->withdraw((int) $tenant->id, $withdrawn['showcase_id']);

        $page = $service->pageGuardianShowcases((int) $tenant->id, 1201, [1201]);

        self::assertSame(1, $page['total']);
        self::assertSame('Stage 1 Achievement', $page['list'][0]['title']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('student is not bound to current guardian');

        $service->pageGuardianShowcases((int) $tenant->id, 1202, [1201]);
    }
}

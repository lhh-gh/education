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

use App\Model\Education\Content\EducationContentReviewRecord;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\ContentReviewService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class ContentReviewServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_review_page_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-review-page');
        $visible = EducationContentReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'learning_material',
            'business_id' => 101,
            'reviewer_id' => 0,
            'status' => 'pending',
        ]);
        EducationContentReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'learning_material',
            'business_id' => 102,
            'reviewer_id' => 0,
            'status' => 'pending',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9902);

        $page = make(ContentReviewService::class)->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testReviewExistingUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_review_handle_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-review-handle');
        $hidden = EducationContentReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'learning_material',
            'business_id' => 201,
            'reviewer_id' => 0,
            'status' => 'pending',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9902);

        $this->expectException(ModelNotFoundException::class);

        make(ContentReviewService::class)->reviewExisting($context, (int) $hidden->id, 9902, 'approved');
    }
}

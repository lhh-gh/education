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

namespace HyperfTests\Feature\Education\Content;

use App\Http\Common\ResultCode;
use App\Model\Education\Content\EducationContentReviewRecord;

/**
 * @internal
 * @coversNothing
 */
final class ContentAdminApiTest extends ContentApiCase
{
    public function testApiFailuresMatchCatalog(): void
    {
        $fixture = $this->contentFixture('content_admin_catalog');
        $this->grantPermissions('education:content:material:save', 'education:content:material:publish', 'education:content:review:handle');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalid = $this->post('/admin/education/content/materials', ['material_name' => 'No code'], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);
        self::assertSame('material_code is required', $invalid['message']);

        $material = $this->post('/admin/education/content/materials', [
            'material_code' => 'ART-LINE-001',
            'material_name' => 'Line Practice',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $material['code']);

        $blocked = $this->post('/admin/education/content/materials/' . $material['data']['material_id'] . '/publish', ['publish_note' => 'ok'], $headers);
        self::assertSame(ResultCode::CONFLICT->value, $blocked['code']);
        self::assertSame('material requires approved review before publish', $blocked['message']);

        $review = EducationContentReviewRecord::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'business_type' => 'learning_material',
            'business_id' => $material['data']['material_id'],
            'reviewer_id' => $this->user->id,
            'status' => 'pending',
        ]);
        $approved = $this->post('/admin/education/content/reviews/' . $review->id . '/review', ['status' => 'approved', 'review_note' => 'ok'], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $approved['code']);

        $published = $this->post('/admin/education/content/materials/' . $material['data']['material_id'] . '/publish', ['publish_note' => 'ok'], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $published['code']);
        self::assertSame('published', $published['data']['status']);
    }
}

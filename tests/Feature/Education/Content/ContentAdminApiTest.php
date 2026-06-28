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
use App\Model\Education\Content\EducationLearningMaterialRelation;
use App\Model\Education\Content\EducationMaterialUsageMetricDaily;
use App\Model\Education\Content\EducationStageAchievementShowcase;
use App\Model\Education\Content\EducationStudentWork;
use App\Model\Education\Content\EducationStudentWorkMetricDaily;

/**
 * @internal
 * @coversNothing
 */
final class ContentAdminApiTest extends ContentApiCase
{
    public function testAdminContentReadAndPublishEndpointsMatchFrontendCatalog(): void
    {
        $fixture = $this->contentFixture('content_admin_frontend_catalog');
        $this->grantPermissions(
            'education:content:material:page',
            'education:content:material:save',
            'education:content:version:page',
            'education:content:relation:page',
            'education:content:relation:save',
            'education:content:student-work:page',
            'education:content:student-work:publish',
            'education:content:student-work:withdraw',
            'education:content:showcase:page',
            'education:content:showcase:publish',
            'education:content:showcase:withdraw',
            'education:content:review:page',
            'education:content:metric:page',
        );
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $material = $this->post('/admin/education/content/materials', [
            'material_code' => 'ART-LINE-READ',
            'material_name' => 'Line Read',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $material['code']);

        EducationLearningMaterialRelation::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'material_id' => $material['data']['material_id'],
            'target_type' => 'course',
            'target_id' => $fixture['course_id'],
            'relation_note' => 'course scope',
        ]);
        $studentWork = EducationStudentWork::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'lesson_id' => $fixture['lesson_id'],
            'teacher_id' => $fixture['teacher_id'],
            'title' => 'Line work',
            'status' => 'draft',
        ]);
        $showcase = EducationStageAchievementShowcase::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'student_id' => $fixture['student_id'],
            'title' => 'Line showcase',
            'status' => 'draft',
        ]);
        EducationContentReviewRecord::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'business_type' => 'learning_material',
            'business_id' => $material['data']['material_id'],
            'reviewer_id' => $this->user->id,
            'status' => 'pending',
        ]);
        EducationMaterialUsageMetricDaily::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'metric_date' => '2026-06-20',
            'material_id' => $material['data']['material_id'],
            'course_id' => $fixture['course_id'],
            'teacher_use_count' => 1,
            'guardian_read_count' => 2,
            'favorite_count' => 3,
        ]);
        EducationStudentWorkMetricDaily::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'metric_date' => '2026-06-20',
            'student_id' => $fixture['student_id'],
            'teacher_id' => $fixture['teacher_id'],
            'created_count' => 1,
            'published_count' => 0,
            'showcase_count' => 1,
            'guardian_read_count' => 2,
        ]);

        foreach ([
            '/admin/education/content/materials',
            '/admin/education/content/material-versions?material_id=' . $material['data']['material_id'],
            '/admin/education/content/material-relations?material_id=' . $material['data']['material_id'],
            '/admin/education/content/student-works',
            '/admin/education/content/showcases',
            '/admin/education/content/reviews',
            '/admin/education/content/material-usage-metrics',
            '/admin/education/content/student-work-metrics',
        ] as $uri) {
            $response = $this->get($uri, [], $headers);
            self::assertSame(ResultCode::SUCCESS->value, $response['code'], $uri);
            self::assertArrayHasKey('list', $response['data'], $uri);
        }

        $workPublished = $this->post('/admin/education/content/student-works/' . $studentWork->id . '/publish', [], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $workPublished['code']);
        self::assertSame('published', $workPublished['data']['status']);

        $showcasePublished = $this->post('/admin/education/content/showcases/' . $showcase->id . '/publish', [], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $showcasePublished['code']);
        self::assertSame('published', $showcasePublished['data']['status']);
    }

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

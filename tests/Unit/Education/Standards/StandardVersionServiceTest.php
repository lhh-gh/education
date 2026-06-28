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

namespace HyperfTests\Unit\Education\Standards;

use App\Model\Education\Standards\EducationCourseLocalizationOverride;
use App\Model\Education\Standards\EducationCourseStandardReviewRecord;
use App\Model\Education\Standards\EducationCourseStandardVersion;
use App\Service\Education\Standards\StandardVersionService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class StandardVersionServiceTest extends StandardsTestCase
{
    public function testPublishRequiresApprovedReviewWhenEnabled(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_publish');
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'service_package',
            'business_id' => 501,
            'version_no' => 1,
            'status' => 'reviewing',
            'snapshot_json' => ['name' => 'Draft'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage('standard version requires approved review before publish');

        make(StandardVersionService::class)->publish((int) $tenant->id, (int) $campus->id, (int) $version->id, 9001, true);
    }

    public function testLocalizationOverrideDoesNotMutatePublishedSnapshot(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_localization');
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => null,
            'business_type' => 'service_package',
            'business_id' => 601,
            'version_no' => 1,
            'status' => 'published',
            'snapshot_json' => ['package_name' => 'Tenant Standard'],
            'published_by' => 9001,
            'published_at' => '2026-06-10 10:00:00',
        ]);

        $result = make(StandardVersionService::class)->saveLocalizationOverride([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'standard_version_id' => $version->id,
            'override_json' => ['package_name' => 'Campus Standard'],
        ]);

        $version->refresh();
        self::assertSame(['package_name' => 'Tenant Standard'], $version->snapshot_json);
        self::assertSame('draft', $result['status']);
        self::assertTrue(EducationCourseLocalizationOverride::query()->where('standard_version_id', $version->id)->where('campus_id', $campus->id)->exists());
    }

    public function testApprovedReviewAllowsPublish(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_publish_approved');
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'service_package',
            'business_id' => 701,
            'version_no' => 1,
            'status' => 'reviewing',
            'snapshot_json' => ['name' => 'Approved'],
        ]);
        EducationCourseStandardReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'service_package',
            'business_id' => 701,
            'standard_version_id' => $version->id,
            'reviewer_id' => 9002,
            'status' => 'approved',
            'reviewed_at' => '2026-06-10 09:00:00',
        ]);

        $result = make(StandardVersionService::class)->publish((int) $tenant->id, (int) $campus->id, (int) $version->id, 9001, true);

        self::assertSame('published', $result['status']);
        self::assertSame('published', EducationCourseStandardVersion::query()->find($version->id)->status->value);
    }

    public function testApprovedReviewMustUseVersionCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_publish_review_campus_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-standards-publish-review-campus');
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'service_package',
            'business_id' => 702,
            'version_no' => 1,
            'status' => 'reviewing',
            'snapshot_json' => ['name' => 'Campus Review'],
        ]);
        EducationCourseStandardReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'service_package',
            'business_id' => 702,
            'standard_version_id' => $version->id,
            'reviewer_id' => 9003,
            'status' => 'approved',
            'reviewed_at' => '2026-06-10 09:00:00',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage('standard version requires approved review before publish');

        make(StandardVersionService::class)->publish((int) $tenant->id, (int) $campus->id, (int) $version->id, 9001, true);
    }

    public function testPublishMustUseCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_publish_campus_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-standards-publish-campus');
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'service_package',
            'business_id' => 703,
            'version_no' => 1,
            'status' => 'reviewing',
            'snapshot_json' => ['name' => 'Hidden Campus Version'],
        ]);
        EducationCourseStandardReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'service_package',
            'business_id' => 703,
            'standard_version_id' => $version->id,
            'reviewer_id' => 9004,
            'status' => 'approved',
            'reviewed_at' => '2026-06-10 09:00:00',
        ]);

        $this->expectException(ModelNotFoundException::class);

        make(StandardVersionService::class)->publish((int) $tenant->id, (int) $campus->id, (int) $version->id, 9001, true);
    }
}

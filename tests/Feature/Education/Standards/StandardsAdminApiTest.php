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

namespace HyperfTests\Feature\Education\Standards;

use App\Http\Common\ResultCode;
use App\Model\Education\Standards\EducationCourseStandardReviewRecord;
use App\Model\Education\Standards\EducationCourseStandardVersion;

/**
 * @internal
 * @coversNothing
 */
final class StandardsAdminApiTest extends StandardsApiCase
{
    public function testApiFailuresMatchCatalog(): void
    {
        $fixture = $this->standardsFixture('standards_api_catalog');
        $this->grantPermissions('education:standards:package:save', 'education:standards:version:publish', 'education:standards:review:handle');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);

        $invalid = $this->post('/admin/education/standards/service-packages', ['package_name' => 'No code'], $headers);
        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $invalid['code']);
        self::assertSame('package_code is required', $invalid['message']);

        $package = $this->post('/admin/education/standards/service-packages', [
            'package_code' => 'ART-BASIC',
            'package_name' => 'Art Basic',
            'course_id' => $fixture['course_id'],
            'guardian_visible' => true,
            'description' => 'basic package',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $package['code']);

        $version = EducationCourseStandardVersion::query()->where('business_type', 'service_package')->where('business_id', $package['data']['service_package_id'])->firstOrFail();
        $blocked = $this->post('/admin/education/standards/versions/' . $version->id . '/publish', ['publish_note' => 'approved'], $headers);
        self::assertSame(ResultCode::CONFLICT->value, $blocked['code']);
        self::assertSame('standard version requires approved review before publish', $blocked['message']);

        $review = EducationCourseStandardReviewRecord::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'business_type' => 'service_package',
            'business_id' => $package['data']['service_package_id'],
            'standard_version_id' => $version->id,
            'reviewer_id' => $this->user->id,
            'status' => 'pending',
        ]);
        $approved = $this->post('/admin/education/standards/reviews/' . $review->id . '/review', ['status' => 'approved', 'review_note' => 'ok'], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $approved['code']);

        $published = $this->post('/admin/education/standards/versions/' . $version->id . '/publish', ['publish_note' => 'approved'], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $published['code']);
        self::assertSame('published', $published['data']['status']);
    }

    public function testLocalizationOverrideDoesNotMutateSnapshot(): void
    {
        $fixture = $this->standardsFixture('standards_api_localization');
        $this->grantPermissions('education:standards:version:localization');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $version = EducationCourseStandardVersion::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => null,
            'business_type' => 'service_package',
            'business_id' => 8801,
            'version_no' => 1,
            'status' => 'published',
            'snapshot_json' => ['package_name' => 'Tenant Standard'],
        ]);

        $result = $this->post('/admin/education/standards/versions/' . $version->id . '/localization-overrides', [
            'override_json' => ['package_name' => 'Campus Standard'],
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        $version->refresh();
        self::assertSame(['package_name' => 'Tenant Standard'], $version->snapshot_json);
    }
}

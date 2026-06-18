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

use App\Model\Education\Standards\EducationCourseServicePackage;
use App\Model\Education\Standards\EducationCourseStandardVersion;
use App\Service\Education\Standards\ServicePackageService;

/**
 * @internal
 * @coversNothing
 */
final class ServicePackageServiceTest extends StandardsTestCase
{
    public function testPublishedPackageEditCreatesNewVersion(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_package');
        $service = make(ServicePackageService::class);
        $created = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'package_code' => 'ART-BASIC',
            'package_name' => 'Art Basic',
            'course_id' => 301,
            'guardian_visible' => true,
            'description' => 'basic package',
        ]);
        EducationCourseServicePackage::query()->whereKey($created['service_package_id'])->update(['status' => 'published']);

        $edited = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'service_package_id' => $created['service_package_id'],
            'package_code' => 'ART-BASIC',
            'package_name' => 'Art Basic 2026',
            'course_id' => 301,
            'guardian_visible' => true,
            'description' => 'new term package',
        ]);

        self::assertNotSame($created['service_package_id'], $edited['service_package_id']);
        self::assertSame(2, $edited['version_no']);
        self::assertSame('draft', $edited['status']);
        self::assertSame('Art Basic', (string) EducationCourseServicePackage::query()->find($created['service_package_id'])->package_name);
        self::assertTrue(EducationCourseStandardVersion::query()->where('business_type', 'service_package')->where('business_id', $edited['service_package_id'])->where('version_no', 2)->exists());
    }
}

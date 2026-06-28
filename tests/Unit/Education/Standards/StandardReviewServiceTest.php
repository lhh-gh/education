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

use App\Model\Education\Standards\EducationCourseStandardReviewRecord;
use App\Service\Education\Standards\StandardReviewService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class StandardReviewServiceTest extends StandardsTestCase
{
    public function testReviewMustUseCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_review_campus_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-standards-review-campus');
        $record = EducationCourseStandardReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'business_type' => 'service_package',
            'business_id' => 801,
            'standard_version_id' => null,
            'reviewer_id' => 9005,
            'status' => 'pending',
        ]);

        $this->expectException(ModelNotFoundException::class);

        make(StandardReviewService::class)->review((int) $tenant->id, (int) $campus->id, (int) $record->id, 9005, 'approved');
    }
}

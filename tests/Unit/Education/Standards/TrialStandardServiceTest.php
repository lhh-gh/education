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

use App\Model\Education\Standards\EducationTrialLessonStandardItem;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Standards\TrialStandardService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class TrialStandardServiceTest extends StandardsTestCase
{
    public function testSaveUsesCurrentCampusScopeForExistingStandardCode(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_trial_save_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-standards-trial-save');
        make(TrialStandardService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'course_id' => 303,
            'standard_code' => 'TRIAL-SHARED',
            'standard_name' => 'Hidden Trial',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9914);

        $this->expectException(ModelNotFoundException::class);

        make(TrialStandardService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => 303,
            'standard_code' => 'TRIAL-SHARED',
            'standard_name' => 'Visible Attempt',
        ], $context);
    }

    public function testTrialStandardItemsAreOrdered(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_trial');
        $service = make(TrialStandardService::class);
        $standard = $service->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'course_id' => 303,
            'standard_code' => 'TRIAL-ART',
            'standard_name' => 'Art Trial',
        ]);

        $service->saveItems((int) $tenant->id, (int) $campus->id, $standard['trial_standard_id'], [
            ['item_name' => 'Warm up', 'item_content' => 'observe', 'sort_order' => 20],
            ['item_name' => 'Main task', 'item_content' => 'paint', 'sort_order' => 10],
        ]);

        $names = EducationTrialLessonStandardItem::query()
            ->where('trial_lesson_standard_id', $standard['trial_standard_id'])
            ->orderBy('sort_order')
            ->pluck('item_name')
            ->all();
        self::assertSame(['Main task', 'Warm up'], $names);
    }
}

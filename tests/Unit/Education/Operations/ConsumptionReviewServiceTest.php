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

namespace HyperfTests\Unit\Education\Operations;

use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Operations\EducationLessonConsumptionAdjustment;
use App\Service\Education\Operations\ConsumptionReviewService;

/**
 * @internal
 * @coversNothing
 */
final class ConsumptionReviewServiceTest extends OperationsTestCase
{
    public function testReviewModeDoesNotConsumeBeforeApproval(): void
    {
        $tenant = $this->tenant('ops_review_pending');
        $campus = $this->campus($tenant);
        $lesson = $this->lessonFixture($tenant, $campus);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);

        $review = make(ConsumptionReviewService::class)->createFromAttendance((int) $lesson->id, $context);

        self::assertSame('pending', $review['status']);
        self::assertSame(0, EducationLessonConsumption::query()->where('tenant_id', $tenant->id)->count());
    }

    public function testAdjustmentNeverDeletesOriginalConsumption(): void
    {
        $tenant = $this->tenant('ops_review_adjust');
        $campus = $this->campus($tenant);
        $lesson = $this->lessonFixture($tenant, $campus);
        $account = $this->accountFixture($tenant, $campus);
        $original = $this->consumptionFixture($tenant, $campus, $account, $lesson);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);

        $adjustment = make(ConsumptionReviewService::class)->createAdjustment([
            'original_consumption_id' => (int) $original->id,
            'credits' => '-1.00',
            'reason' => 'wrong attendance status',
        ], $context);

        self::assertNotNull(EducationLessonConsumption::query()->find($original->id));
        self::assertSame('reversed', EducationLessonConsumption::query()->find($original->id)->status);
        self::assertNotNull(EducationLessonConsumption::query()->find($adjustment['adjustment_consumption_id']));
        self::assertSame(1, EducationLessonConsumptionAdjustment::query()->where('tenant_id', $tenant->id)->where('original_consumption_id', $original->id)->count());
    }
}

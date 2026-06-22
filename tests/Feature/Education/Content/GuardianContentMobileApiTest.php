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
use App\Model\Education\Content\EducationMaterialReadRecord;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\LearningMaterialService;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class GuardianContentMobileApiTest extends ContentApiCase
{
    public function testGuardianMaterialReadRecordCreatedOnce(): void
    {
        $fixture = $this->contentFixture('content_guardian_mobile', 'guardian');
        $created = make(LearningMaterialService::class)->save([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'material_code' => 'ART-GUARDIAN-001',
            'material_name' => 'Guardian Material',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ]);
        $context = new EducationUserContext(
            userId: $this->user->id,
            tenantId: $fixture['tenant_id'],
            roleCode: EducationRoleCode::Guardian,
            platformAccess: false,
            campusIds: [$fixture['campus_id']],
            currentCampusId: $fixture['campus_id']
        );
        make(LearningMaterialService::class)->publish($context, $created['material_id'], $this->user->id, false);

        $headers = $this->mobileHeaders($fixture['tenant']);
        $first = $this->get('/mobile/education/content/guardian/students/' . $fixture['student_id'] . '/materials', [], $headers);
        $second = $this->get('/mobile/education/content/guardian/students/' . $fixture['student_id'] . '/materials', [], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $first['code']);
        self::assertSame(1, $first['data']['total']);
        self::assertSame(ResultCode::SUCCESS->value, $second['code']);
        self::assertSame(1, EducationMaterialReadRecord::query()->where('material_id', $created['material_id'])->count());
    }
}

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
use App\Service\Education\Content\LearningMaterialService;

/**
 * @internal
 * @coversNothing
 */
final class TeacherContentMobileApiTest extends ContentApiCase
{
    public function testTeacherMaterialsUseCurrentCampusScope(): void
    {
        $fixture = $this->contentFixture('content_teacher_mobile_scope', 'teacher');
        $hiddenCampus = $this->campus($fixture['tenant'], 'hidden-content-teacher-mobile');
        $visible = make(LearningMaterialService::class)->save([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'material_code' => 'ART-TEACHER-SCOPE-001',
            'material_name' => 'Visible Teacher Material',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
        ]);
        make(LearningMaterialService::class)->save([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'ART-TEACHER-SCOPE-HIDDEN',
            'material_name' => 'Hidden Teacher Material',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
        ]);

        $response = $this->get('/mobile/education/content/teacher/materials', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $response['code']);
        self::assertSame(1, $response['data']['total']);
        self::assertSame($visible['material_id'], (int) $response['data']['list'][0]['id']);
    }

    public function testTeacherMaterialAccessRequiresCourseAuthorization(): void
    {
        $fixture = $this->contentFixture('content_teacher_mobile', 'teacher');
        make(LearningMaterialService::class)->save([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'material_code' => 'ART-TEACHER-001',
            'material_name' => 'Teacher Material',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
        ]);

        $unauthorized = $this->get('/mobile/education/content/teacher/materials', ['course_id' => 999001], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $unauthorized['code']);
        self::assertSame('teacher is not authorized for this course', $unauthorized['message']);
    }
}

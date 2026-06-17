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

namespace HyperfTests\Unit\Education\Family;

use App\Exception\BusinessException;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Family\FamilyMessageService;

/**
 * @internal
 * @coversNothing
 */
final class FamilyMessageServiceTest extends FamilyTestCase
{
    public function testFamilyMessageThreadIsStudentScoped(): void
    {
        $fixture = $this->familyFixture('family_message');
        $service = make(FamilyMessageService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Guardian, [$fixture['campus_id']], $fixture['guardian_user_id']);

        $message = $service->send([
            'student_id' => $fixture['student_id'],
            'thread_id' => 'student-' . $fixture['student_id'],
            'content' => 'Please check homework',
        ], $context);

        self::assertSame('sent', $message['status']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $service->send([
            'student_id' => $fixture['other_student_id'],
            'thread_id' => 'student-' . $fixture['other_student_id'],
            'content' => 'Cross-student message',
        ], $context);
    }
}

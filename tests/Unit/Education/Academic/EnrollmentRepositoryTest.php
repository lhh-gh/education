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

namespace HyperfTests\Unit\Education\Academic;

use App\Repository\Education\Academic\EnrollmentRepository;

/**
 * @internal
 * @coversNothing
 */
final class EnrollmentRepositoryTest extends AcademicTestCase
{
    public function testNextEnrollmentNoIsUniquePerCall(): void
    {
        $repository = make(EnrollmentRepository::class);

        $first = $repository->nextEnrollmentNo(1001, 2001);
        $second = $repository->nextEnrollmentNo(1001, 2001);

        self::assertStringStartsWith('ENR', $first);
        self::assertStringStartsWith('ENR', $second);
        self::assertNotSame($first, $second);
    }
}

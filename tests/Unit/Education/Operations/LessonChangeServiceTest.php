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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Operations\EducationLessonChangeLog;
use App\Service\Education\Operations\LessonChangeService;

/**
 * @internal
 * @coversNothing
 */
final class LessonChangeServiceTest extends OperationsTestCase
{
    public function testRescheduleRejectsTeacherConflict(): void
    {
        $tenant = $this->tenant('ops_lesson_conflict');
        $campus = $this->campus($tenant);
        $conflict = $this->lessonFixture($tenant, $campus);
        $source = $this->lessonFixture($tenant, $campus, [
            'lesson_no' => 'L' . uniqid(),
            'teacher_id' => $conflict->teacher_id,
            'start_at' => '2026-06-10 12:00:00',
            'end_at' => '2026-06-10 13:00:00',
        ]);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);
        $service = make(LessonChangeService::class);
        $request = $service->createRequest([
            'lesson_id' => (int) $source->id,
            'change_type' => 'reschedule',
            'new_values_json' => ['start_time' => '2026-06-10 10:30:00', 'end_time' => '2026-06-10 11:30:00'],
            'reason' => 'teacher training',
        ], $context);

        try {
            $service->approve((int) $request['id'], [], $context);
            self::fail('Expected teacher conflict.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame((int) $conflict->id, $exception->getResponse()->data['conflict_lesson_id']);
        }
    }

    public function testApplyChangeWritesBeforeAfterLog(): void
    {
        $tenant = $this->tenant('ops_lesson_apply');
        $campus = $this->campus($tenant);
        $lesson = $this->lessonFixture($tenant, $campus);
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);
        $service = make(LessonChangeService::class);
        $request = $service->createRequest([
            'lesson_id' => (int) $lesson->id,
            'change_type' => 'reschedule',
            'new_values_json' => ['start_time' => '2026-06-10 14:00:00', 'end_time' => '2026-06-10 15:30:00'],
            'reason' => 'teacher training',
        ], $context);

        $service->approve((int) $request['id'], [], $context);
        $applied = $service->apply((int) $request['id'], $context);

        self::assertSame('applied', $applied['status']);
        self::assertSame('2026-06-10 14:00:00', EducationLesson::query()->find($lesson->id)->start_at->toDateTimeString());
        self::assertSame(1, EducationLessonChangeLog::query()->where('tenant_id', $tenant->id)->where('lesson_id', $lesson->id)->count());
    }
}

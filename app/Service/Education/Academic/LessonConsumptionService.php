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

namespace App\Service\Education\Academic;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\Education\Academic\ConsumptionRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class LessonConsumptionService
{
    public function __construct(
        private readonly ConsumptionRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function detail(int $id, EducationUserContext $context): EducationLessonConsumption
    {
        $row = $this->repository->findScoped($id, $context);
        if (! $row instanceof EducationLessonConsumption) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'consumption not found', ['id' => $id]);
        }

        return $row;
    }

    public function createForAttendance(EducationLessonAttendance $attendance, EducationStudentCourseAccount $account, string $units, EducationUserContext $context, ?int $operatorId): EducationLessonConsumption
    {
        return $this->repository->createConsumption([
            'tenant_id' => (int) $attendance->tenant_id,
            'campus_id' => (int) $attendance->campus_id,
            'consumption_no' => $this->repository->nextConsumptionNo((int) $attendance->tenant_id, (int) $attendance->campus_id),
            'account_id' => (int) $account->id,
            'student_id' => (int) $attendance->student_id,
            'course_id' => (int) $attendance->course_id,
            'lesson_id' => (int) $attendance->lesson_id,
            'lesson_student_id' => (int) $attendance->lesson_student_id,
            'attendance_id' => (int) $attendance->id,
            'source_type' => 'attendance',
            'direction' => 'decrease',
            'units' => $this->decimal($units),
            'before_available_units' => $account->available_units,
            'after_available_units' => $this->subtract($account->available_units, $units),
            'before_consumed_units' => $account->consumed_units,
            'after_consumed_units' => $this->add($account->consumed_units, $units),
            'status' => 'active',
            'reason' => 'attendance',
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]);
    }

    public function rollback(int $id, string $reason, EducationUserContext $context, ?int $operatorId): array
    {
        $original = $this->repository->findActiveForRollback($id, $context);
        if (! $original instanceof EducationLessonConsumption) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'consumption not found', ['id' => $id]);
        }
        if ($original->status === 'reversed') {
            throw new BusinessException(ResultCode::CONFLICT, 'consumption is already reversed', ['id' => $id]);
        }
        if ($this->repository->hasRollback((int) $original->id, (int) $original->tenant_id)) {
            throw new BusinessException(ResultCode::CONFLICT, 'consumption rollback already exists', ['id' => $id]);
        }

        return Db::transaction(function () use ($original, $reason, $context, $operatorId): array {
            $account = EducationStudentCourseAccount::query()->whereKey($original->account_id)->lockForUpdate()->first();
            if (! $account instanceof EducationStudentCourseAccount) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'student course account not found', ['account_id' => (int) $original->account_id]);
            }
            $beforeAvailable = $this->decimal($account->available_units);
            $beforeConsumed = $this->decimal($account->consumed_units);
            $afterAvailable = $this->add($beforeAvailable, $original->units);
            $afterConsumed = $this->subtract($beforeConsumed, $original->units);
            $rollback = $this->repository->createRollback([
                'tenant_id' => (int) $original->tenant_id,
                'campus_id' => (int) $original->campus_id,
                'consumption_no' => $this->repository->nextConsumptionNo((int) $original->tenant_id, (int) $original->campus_id),
                'account_id' => (int) $original->account_id,
                'student_id' => (int) $original->student_id,
                'course_id' => (int) $original->course_id,
                'lesson_id' => (int) $original->lesson_id,
                'lesson_student_id' => (int) $original->lesson_student_id,
                'attendance_id' => (int) $original->attendance_id,
                'source_type' => 'rollback',
                'direction' => 'increase',
                'units' => $this->decimal($original->units),
                'before_available_units' => $beforeAvailable,
                'after_available_units' => $afterAvailable,
                'before_consumed_units' => $beforeConsumed,
                'after_consumed_units' => $afterConsumed,
                'status' => 'active',
                'original_consumption_id' => (int) $original->id,
                'reason' => $reason,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);
            $account->available_units = $afterAvailable;
            $account->consumed_units = $afterConsumed;
            $account->updated_by = $operatorId;
            $account->save();
            $original->status = 'reversed';
            $original->reversed_at = Carbon::now()->toDateTimeString();
            $original->reversed_by = $operatorId;
            $original->updated_by = $operatorId;
            $original->save();
            EducationLessonAttendance::query()->whereKey($original->attendance_id)->update([
                'consumption_status' => 'reversed',
                'updated_by' => $operatorId,
            ]);
            $this->dispatchAudit($original->refresh(), $rollback, $context);

            return [
                'original' => $original->refresh()->toArray(),
                'rollback' => $rollback->refresh()->toArray(),
                'account' => $account->refresh()->toArray(),
            ];
        });
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function add(string $left, string $right): string
    {
        return number_format((float) $left + (float) $right, 2, '.', '');
    }

    private function subtract(string $left, string $right): string
    {
        return number_format((float) $left - (float) $right, 2, '.', '');
    }

    private function dispatchAudit(EducationLessonConsumption $original, EducationLessonConsumption $rollback, EducationUserContext $context): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'consumption',
            action: 'education.academic.consumption.rollback',
            businessType: 'consumption',
            businessId: (int) $original->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['original' => $original->toArray(), 'rollback' => $rollback->toArray()],
            metadata: ['tenant_id' => (int) $original->tenant_id, 'campus_id' => (int) $original->campus_id],
            summary: 'Consumption rollback'
        ));
    }
}

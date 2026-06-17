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

namespace App\Service\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Education\Operations\EducationMakeupRecord;
use App\Repository\Education\Operations\MakeupEntitlementRepository;
use App\Repository\Education\Operations\MakeupRecordRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class MakeupService
{
    public function __construct(
        private readonly MakeupEntitlementRepository $entitlementRepository,
        private readonly MakeupRecordRepository $recordRepository
    ) {}

    public function createEntitlementFromLeave(int $leaveRequestId, EducationUserContext $context): array
    {
        $leave = EducationLeaveRequest::query()
            ->where('tenant_id', $context->tenantId)
            ->whereKey($leaveRequestId)
            ->first();
        if (! $leave instanceof EducationLeaveRequest) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'leave request not found', ['leave_request_id' => $leaveRequestId]);
        }
        if ($leave->status !== 'approved' || ! $leave->makeup_required) {
            throw new BusinessException(ResultCode::CONFLICT, 'leave request is not eligible for make-up', ['leave_request_id' => $leaveRequestId]);
        }

        $entitlement = $this->entitlementRepository->createFromLeave([
            'tenant_id' => (int) $leave->tenant_id,
            'campus_id' => (int) $leave->campus_id,
            'student_id' => (int) $leave->student_id,
            'course_id' => (int) $leave->course_id,
            'source_lesson_id' => (int) $leave->lesson_id,
            'source_leave_request_id' => (int) $leave->id,
            'status' => 'available',
            'expires_at' => Carbon::now()->addDays(90)->endOfDay()->toDateTimeString(),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $entitlement->toArray();
    }

    public function arrangeMakeup(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $entitlement = $this->entitlement((int) $data['makeup_entitlement_id'], $context);
            if ($entitlement->status === 'expired') {
                throw new BusinessException(ResultCode::CONFLICT, 'makeup entitlement is expired', ['id' => (int) $entitlement->id]);
            }
            if ($entitlement->status !== 'available') {
                throw new BusinessException(ResultCode::CONFLICT, 'makeup entitlement is not available', ['id' => (int) $entitlement->id, 'status' => $entitlement->status]);
            }
            $record = $this->recordRepository->create([
                'tenant_id' => (int) $entitlement->tenant_id,
                'campus_id' => (int) $entitlement->campus_id,
                'makeup_entitlement_id' => (int) $entitlement->id,
                'student_id' => (int) $entitlement->student_id,
                'makeup_lesson_id' => (int) $data['makeup_lesson_id'],
                'status' => 'arranged',
                'arranged_by' => $context->userId,
                'arranged_at' => Carbon::parse((string) $data['arranged_at'])->toDateTimeString(),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);

            return ['makeup_record_id' => (int) $record->id, 'status' => $record->status];
        });
    }

    public function completeMakeupAttendance(int $makeupRecordId, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($makeupRecordId, $context): array {
            $record = $this->recordRepository->lockRecord($context->tenantId, $makeupRecordId);
            if (! $record instanceof EducationMakeupRecord) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'makeup record not found', ['makeup_record_id' => $makeupRecordId]);
            }
            $entitlement = $this->entitlementRepository->lockEntitlement($context->tenantId, (int) $record->makeup_entitlement_id);
            if (! $entitlement instanceof EducationMakeupEntitlement) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'makeup entitlement not found', ['id' => (int) $record->makeup_entitlement_id]);
            }
            if ($record->status === 'completed' || $entitlement->status === 'used') {
                return ['makeup_record_id' => (int) $record->id, 'entitlement_status' => 'used'];
            }
            $record->update(['status' => 'completed', 'completed_at' => Carbon::now()->toDateTimeString(), 'updated_by' => $context->userId]);
            $this->entitlementRepository->markUsed($entitlement, (int) $record->makeup_lesson_id, Carbon::now()->toDateTimeString());

            return ['makeup_record_id' => (int) $record->id, 'entitlement_status' => 'used'];
        });
    }

    public function cancelArrangement(int $makeupRecordId, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($makeupRecordId, $context): array {
            $record = $this->recordRepository->lockRecord($context->tenantId, $makeupRecordId);
            if (! $record instanceof EducationMakeupRecord) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'makeup record not found', ['makeup_record_id' => $makeupRecordId]);
            }
            $record->update(['status' => 'cancelled', 'updated_by' => $context->userId]);
            $entitlement = $this->entitlementRepository->lockEntitlement($context->tenantId, (int) $record->makeup_entitlement_id);
            if ($entitlement instanceof EducationMakeupEntitlement) {
                $this->entitlementRepository->restoreAvailable($entitlement);
            }

            return ['makeup_record_id' => (int) $record->id, 'status' => 'cancelled'];
        });
    }

    private function entitlement(int $id, EducationUserContext $context): EducationMakeupEntitlement
    {
        $entitlement = $this->entitlementRepository->lockEntitlement($context->tenantId, $id);
        if (! $entitlement instanceof EducationMakeupEntitlement) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'makeup entitlement not found', ['id' => $id]);
        }

        return $entitlement;
    }
}

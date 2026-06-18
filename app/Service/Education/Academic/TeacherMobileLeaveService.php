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
use App\Model\Education\Academic\EducationLeaveRequest;
use App\Repository\Education\Academic\TeacherMobileLeaveRepository;
use App\Schema\Education\Academic\TeacherMobileLeaveSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TeacherMobileLeaveService
{
    public function __construct(
        private readonly TeacherMobileContextResolver $contextResolver,
        private readonly TeacherMobileLeaveRepository $repository,
        private readonly TeacherMobileLeaveSchema $schema,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, EducationUserContext $context): array
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        if ($campusId !== null) {
            $params['campus_id'] = $campusId;
        }

        $result = $this->repository->pageAssignedLeave(
            $params,
            max(1, (int) ($params['page'] ?? 1)),
            max(1, min(100, (int) ($params['pageSize'] ?? 20))),
            $context,
            (int) $teacher->id
        );
        $result['list'] = array_map(fn (array $row): array => $this->schema->leave($this->repository->getModel()->newFromBuilder($row)), $result['list']);

        return $result;
    }

    public function detail(int $id, array $params, EducationUserContext $context): array
    {
        return $this->schema->leave($this->assignedLeave($id, $params, $context));
    }

    public function approve(int $id, array $payload, EducationUserContext $context): array
    {
        return $this->review($id, $payload, $context, 'approved', 'education.academic.teacher_mobile.leave_approved');
    }

    public function reject(int $id, array $payload, EducationUserContext $context): array
    {
        return $this->review($id, $payload, $context, 'rejected', 'education.academic.teacher_mobile.leave_rejected');
    }

    private function review(int $id, array $payload, EducationUserContext $context, string $status, string $action): array
    {
        $this->assignedLeave($id, $payload, $context);
        $updated = $this->repository->updateReview(
            id: $id,
            status: $status,
            reviewRemark: (string) $payload['review_remark'],
            operatorId: $this->contextResolver->currentOperatorId($context)
        );
        $this->dispatchAudit($updated, $context, $action);

        return $this->schema->leave($updated);
    }

    private function assignedLeave(int $id, array $params, EducationUserContext $context): EducationLeaveRequest
    {
        $teacher = $this->contextResolver->resolveTeacher($context);
        $campusId = $this->contextResolver->assertCampusAllowed($context, isset($params['campus_id']) ? (int) $params['campus_id'] : null);
        $leave = $this->repository->findAssignedLeave($id, $context, (int) $teacher->id, $campusId);
        if (! $leave instanceof EducationLeaveRequest) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'leave request not found in current teacher context', ['id' => $id]);
        }

        return $leave;
    }

    private function dispatchAudit(EducationLeaveRequest $leave, EducationUserContext $context, string $action): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'teacher_mobile_leave',
            action: $action,
            businessType: 'leave_request',
            businessId: (int) $leave->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['id' => (int) $leave->id, 'status' => (string) $leave->status],
            metadata: ['tenant_id' => (int) $leave->tenant_id, 'campus_id' => (int) $leave->campus_id],
            summary: 'Teacher mobile leave reviewed'
        ));
    }
}

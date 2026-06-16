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
use App\Model\Education\Academic\EducationLessonStudent;
use App\Repository\Education\Academic\GuardianMobileRepository;
use App\Schema\Education\Academic\GuardianLeaveSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Psr\EventDispatcher\EventDispatcherInterface;

final class GuardianMobileLeaveService
{
    public function __construct(
        private readonly GuardianMobileContextResolver $contextResolver,
        private readonly GuardianMobileRepository $repository,
        private readonly LeaveRequestService $leaveRequestService,
        private readonly GuardianLeaveSchema $schema,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function create(array $payload, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $lessonStudent = EducationLessonStudent::query()
            ->where('tenant_id', (int) $context->tenantId)
            ->whereKey((int) $payload['lesson_student_id'])
            ->first();
        if (! $lessonStudent instanceof EducationLessonStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lesson student not found', ['lesson_student_id' => (int) $payload['lesson_student_id']]);
        }

        $binding = $this->repository->assertBoundStudent((int) $context->tenantId, (int) $guardian->id, (int) $lessonStudent->student_id);
        if ($binding === []) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => (int) $lessonStudent->student_id]);
        }
        if (! (bool) $binding['can_submit_leave']) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian cannot submit leave for student', ['student_id' => (int) $lessonStudent->student_id]);
        }

        $leaveContext = new EducationUserContext(
            userId: $context->userId,
            tenantId: $context->tenantId,
            roleCode: $context->roleCode,
            platformAccess: $context->platformAccess,
            campusIds: [(int) $lessonStudent->campus_id],
            currentCampusId: (int) $lessonStudent->campus_id
        );

        $leave = $this->leaveRequestService->create([
            'lesson_student_id' => (int) $lessonStudent->id,
            'source' => 'guardian',
            'guardian_id' => (int) $guardian->id,
            'leave_type' => (string) $payload['leave_type'],
            'reason' => (string) $payload['reason'],
            'makeup_required' => (bool) ($payload['makeup_required'] ?? true),
        ], $leaveContext, $this->contextResolver->currentOperatorId($context));
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'guardian_mobile_leave',
            action: 'education.academic.guardian_mobile.leave_created',
            businessType: 'leave_request',
            businessId: (int) $leave->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['id' => (int) $leave->id, 'status' => (string) $leave->status],
            metadata: ['tenant_id' => (int) $leave->tenant_id, 'campus_id' => (int) $leave->campus_id],
            summary: 'Guardian mobile leave created',
            actorType: 'guardian'
        ));

        return $this->schema->leave($leave);
    }
}

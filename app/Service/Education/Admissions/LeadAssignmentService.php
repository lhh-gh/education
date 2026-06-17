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

namespace App\Service\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLead;
use App\Repository\Education\Admissions\LeadAssignmentRepository;
use App\Repository\Education\Admissions\LeadRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class LeadAssignmentService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly LeadAssignmentRepository $assignmentRepository
    ) {}

    public function assign(int $leadId, int $toUserId, string $reason, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($leadId, $toUserId, $reason, $context): array {
            $lead = $this->leadRepository->lockScoped($leadId, $context);
            if (! $lead instanceof EducationLead) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'lead not found in current context', ['id' => $leadId]);
            }

            $active = $this->assignmentRepository->activeForLead($context->tenantId, $leadId);
            if ($active !== null) {
                $active->update(['status' => 'replaced', 'updated_by' => $context->userId]);
            }

            $assignment = $this->assignmentRepository->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => $lead->campus_id,
                'lead_id' => $leadId,
                'from_user_id' => $lead->owner_user_id,
                'to_user_id' => $toUserId,
                'status' => 'active',
                'assigned_at' => Carbon::now()->toDateTimeString(),
                'reason' => $reason,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $lead->update([
                'owner_user_id' => $toUserId,
                'stage' => $lead->stage === 'new' ? 'assigned' : $lead->stage,
                'updated_by' => $context->userId,
            ]);

            return $assignment->refresh()->toArray() + ['lead_owner_user_id' => $toUserId];
        });
    }
}

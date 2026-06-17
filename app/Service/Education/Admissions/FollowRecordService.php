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
use App\Repository\Education\Admissions\FollowRecordRepository;
use App\Repository\Education\Admissions\LeadRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class FollowRecordService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly FollowRecordRepository $repository
    ) {}

    public function create(int $leadId, array $data, EducationUserContext $context): array
    {
        $lead = $this->leadRepository->findScoped($leadId, $context);
        if (! $lead instanceof EducationLead) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lead not found in current context', ['id' => $leadId]);
        }

        $record = $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $lead->campus_id,
            'lead_id' => $leadId,
            'follow_type' => $data['follow_type'] ?? 'phone',
            'content' => trim((string) $data['content']),
            'next_follow_at' => $data['next_follow_at'] ?? null,
            'result' => $data['result'] ?? 'continued',
            'operator_user_id' => $context->userId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $lead->update([
            'stage' => 'followed',
            'last_follow_at' => Carbon::now()->toDateTimeString(),
            'next_follow_at' => $data['next_follow_at'] ?? $lead->next_follow_at,
            'updated_by' => $context->userId,
        ]);

        return ['follow_record_id' => (int) $record->id, 'lead_stage' => 'followed'];
    }
}

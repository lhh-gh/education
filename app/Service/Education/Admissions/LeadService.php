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
use App\Model\Education\Admissions\EducationLeadGuardian;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Repository\Education\Admissions\LeadRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class LeadService
{
    public function __construct(private readonly LeadRepository $repository) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function detail(int $id, EducationUserContext $context): array
    {
        $lead = $this->repository->findScoped($id, $context);
        if (! $lead instanceof EducationLead) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lead not found in current context', ['id' => $id]);
        }

        return $lead->toArray() + [
            'guardians' => EducationLeadGuardian::query()->where('tenant_id', $context->tenantId)->where('lead_id', $id)->get()->toArray(),
            'students' => EducationLeadStudent::query()->where('tenant_id', $context->tenantId)->where('lead_id', $id)->get()->toArray(),
        ];
    }

    public function create(array $data, EducationUserContext $context): array
    {
        $mobile = trim((string) $data['contact_mobile']);
        $existing = $this->repository->findByMobile($context->tenantId, $mobile);
        if ($existing instanceof EducationLead) {
            throw new BusinessException(ResultCode::CONFLICT, 'lead mobile already exists', ['lead_id' => (int) $existing->id]);
        }

        $campusId = isset($data['campus_id']) ? (int) $data['campus_id'] : $context->currentCampusId;
        $lead = $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $campusId,
            'lead_no' => $this->repository->nextLeadNo($context->tenantId, $campusId),
            'source_id' => $data['source_id'] ?? null,
            'contact_name' => trim((string) $data['contact_name']),
            'contact_mobile' => $mobile,
            'contact_wechat' => $data['contact_wechat'] ?? null,
            'stage' => 'new',
            'status' => 'active',
            'owner_user_id' => $data['owner_user_id'] ?? null,
            'intention_course_id' => $data['intention_course_id'] ?? null,
            'intention_level' => $data['intention_level'] ?? 'medium',
            'next_follow_at' => $data['next_follow_at'] ?? null,
            'remark' => $data['remark'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        $this->createLeadChildren($lead, $data, $context);

        return $lead->refresh()->toArray();
    }

    private function createLeadChildren(EducationLead $lead, array $data, EducationUserContext $context): void
    {
        $guardians = $data['lead_guardians'] ?? [[
            'name' => $lead->contact_name,
            'mobile' => $lead->contact_mobile,
            'relation' => 'parent',
            'wechat' => $lead->contact_wechat,
            'is_primary' => true,
        ]];
        foreach ($guardians as $guardian) {
            EducationLeadGuardian::query()->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => $lead->campus_id,
                'lead_id' => (int) $lead->id,
                'name' => trim((string) $guardian['name']),
                'mobile' => trim((string) $guardian['mobile']),
                'relation' => $guardian['relation'] ?? 'parent',
                'wechat' => $guardian['wechat'] ?? null,
                'is_primary' => (bool) ($guardian['is_primary'] ?? false),
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
        }

        foreach (($data['lead_students'] ?? []) as $student) {
            EducationLeadStudent::query()->create([
                'tenant_id' => $context->tenantId,
                'campus_id' => $lead->campus_id,
                'lead_id' => (int) $lead->id,
                'name' => trim((string) $student['name']),
                'gender' => $student['gender'] ?? 'unknown',
                'birthday' => $student['birthday'] ?? null,
                'grade' => $student['grade'] ?? null,
                'school' => $student['school'] ?? null,
                'intention_course_id' => $student['intention_course_id'] ?? $lead->intention_course_id,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
        }
    }
}

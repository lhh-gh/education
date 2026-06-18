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
use App\Repository\Education\Admissions\LeadSourceRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class LeadSourceService
{
    public function __construct(private readonly LeadSourceRepository $repository) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function create(array $data, EducationUserContext $context): array
    {
        $code = trim((string) $data['code']);
        if ($this->repository->findByCode($context->tenantId, $code) !== null) {
            throw new BusinessException(ResultCode::CONFLICT, 'lead source code already exists', ['code' => $code]);
        }

        return $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $data['campus_id'] ?? $context->currentCampusId,
            'code' => $code,
            'name' => trim((string) $data['name']),
            'channel_type' => (string) ($data['channel_type'] ?? 'offline'),
            'default_consultant_id' => $data['default_consultant_id'] ?? null,
            'status' => $data['status'] ?? 'enabled',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'remark' => $data['remark'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ])->toArray();
    }
}

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

namespace App\Service\Education\Group;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Repository\Education\Group\ApprovalTemplateRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;

final class ApprovalTemplateService
{
    public function __construct(private readonly ApprovalTemplateRepository $repository) {}

    /**
     * @param array<string, mixed> $data
     * @return array{template_id: int, status: string}
     */
    public function save(array $data, EducationUserContext $context): array
    {
        return Db::transaction(function () use ($data, $context): array {
            $nodes = $data['nodes'] ?? [];
            if (! \is_array($nodes) || $nodes === []) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'approval nodes are required');
            }
            $template = $this->repository->createTemplate([
                'tenant_id' => (int) $context->tenantId,
                'campus_id' => $context->currentCampusId,
                'template_code' => (string) ($data['template_code'] ?? ''),
                'template_name' => (string) ($data['template_name'] ?? ''),
                'business_type' => (string) ($data['business_type'] ?? ''),
                'status' => (string) ($data['status'] ?? 'enabled'),
                'version' => (int) ($data['version'] ?? 1),
                'config_json' => \is_array($data['config_json'] ?? null) ? $data['config_json'] : null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            foreach ($nodes as $index => $node) {
                if (! \is_array($node)) {
                    continue;
                }
                $this->repository->createNode([
                    'tenant_id' => (int) $context->tenantId,
                    'campus_id' => $context->currentCampusId,
                    'template_id' => (int) $template->id,
                    'node_code' => (string) ($node['node_code'] ?? ('node_' . $index)),
                    'node_name' => (string) ($node['node_name'] ?? 'Node'),
                    'sort_order' => (int) ($node['sort_order'] ?? ($index + 1)),
                    'assignee_type' => (string) ($node['assignee_type'] ?? 'user'),
                    'assignee_value_json' => ['user_ids' => [(int) ($node['assignee_user_id'] ?? 0)]],
                    'created_by' => $context->userId,
                    'updated_by' => $context->userId,
                ]);
            }

            return ['template_id' => (int) $template->id, 'status' => (string) $template->status];
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }
}

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

namespace App\Service\Education\Workflow;

use App\Repository\Education\Workflow\WorkflowTemplateRepository;

final class WorkflowTemplateService
{
    public function __construct(
        private readonly WorkflowTemplateRepository $templateRepository
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{template_id: int}
     */
    public function save(array $data): array
    {
        $template = $this->templateRepository->create($data);

        return ['template_id' => (int) $template->id];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        return $this->templateRepository->page($tenantId, $filters, $page, $pageSize);
    }
}

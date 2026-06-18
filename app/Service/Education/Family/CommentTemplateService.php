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

namespace App\Service\Education\Family;

use App\Repository\Education\Family\LessonCommentRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class CommentTemplateService
{
    public function __construct(private readonly LessonCommentRepository $repository) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTemplates(array $filters, EducationUserContext $context): array
    {
        return $this->repository->pageTemplates($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function saveTemplate(array $data, EducationUserContext $context): array
    {
        $template = $this->repository->saveTemplate($data + [
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $template->toArray();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTags(array $filters, EducationUserContext $context): array
    {
        return $this->repository->pageTags($filters, $context);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function saveTag(array $data, EducationUserContext $context): array
    {
        $tag = $this->repository->saveTag($data + [
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $tag->toArray();
    }
}

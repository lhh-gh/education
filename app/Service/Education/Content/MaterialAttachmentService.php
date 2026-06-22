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

namespace App\Service\Education\Content;

use App\Repository\Education\Content\MaterialAttachmentRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class MaterialAttachmentService
{
    public function __construct(private readonly MaterialAttachmentRepository $attachments) {}

    /**
     * @param list<array<string, mixed>> $attachments
     */
    public function replaceForVersion(EducationUserContext $context, int $versionId, array $attachments): void
    {
        $this->attachments->replaceForVersion($context, $versionId, $attachments);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForVersion(EducationUserContext $context, int $versionId): array
    {
        return $this->attachments->listForVersion($context, $versionId);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->attachments->page($filters, $context, $page, $pageSize);
    }
}

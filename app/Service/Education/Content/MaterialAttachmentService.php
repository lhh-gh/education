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

final class MaterialAttachmentService
{
    public function __construct(private readonly MaterialAttachmentRepository $attachments) {}

    /**
     * @param list<array<string, mixed>> $attachments
     */
    public function replaceForVersion(int $tenantId, ?int $campusId, int $versionId, array $attachments): void
    {
        $this->attachments->replaceForVersion($tenantId, $campusId, $versionId, $attachments);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForVersion(int $tenantId, int $versionId): array
    {
        return $this->attachments->listForVersion($tenantId, $versionId);
    }
}

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

namespace App\Service\Education\Standards;

use App\Repository\Education\Standards\StandardVersionRepository;

final class StandardVersionService
{
    public function __construct(private readonly StandardVersionRepository $versions) {}

    /**
     * @return array{standard_version_id: int, status: string}
     */
    public function publish(int $tenantId, int $versionId, int $operatorId, bool $requiresApprovedReview = true): array
    {
        $version = $this->versions->findInTenant($tenantId, $versionId);
        if ($requiresApprovedReview && ! $this->versions->hasApprovedReview($version)) {
            throw new \RuntimeException('standard version requires approved review before publish', 409);
        }

        $this->versions->publish($version, $operatorId);

        return ['standard_version_id' => (int) $version->id, 'status' => 'published'];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{localization_override_id: int, status: string}
     */
    public function saveLocalizationOverride(array $data): array
    {
        $override = $this->versions->saveLocalizationOverride($data + ['status' => 'draft']);

        return ['localization_override_id' => (int) $override->id, 'status' => $this->statusValue($override->status)];
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}

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

use App\Repository\Education\Standards\ServicePackageRepository;
use App\Repository\Education\Standards\StandardVersionRepository;

final class ServicePackageService
{
    public function __construct(
        private readonly ServicePackageRepository $packages,
        private readonly StandardVersionRepository $versions
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{service_package_id: int, version_no: int, status: string}
     */
    public function save(array $data): array
    {
        $tenantId = (int) $data['tenant_id'];
        $packageId = isset($data['service_package_id']) ? (int) $data['service_package_id'] : null;

        if ($packageId !== null) {
            $existing = $this->packages->findInTenant($tenantId, $packageId);
            if ($this->statusValue($existing->status) === 'published') {
                unset($data['service_package_id'], $data['id']);
                $data['version_no'] = $this->packages->nextVersionNo($tenantId, (string) $existing->package_code);
                $data['status'] = 'draft';
            } else {
                $data['id'] = $packageId;
            }
        }

        $package = $this->packages->save($data + ['version_no' => 1, 'status' => 'draft', 'guardian_visible' => false]);
        $this->versions->saveSnapshot(
            (int) $package->tenant_id,
            $package->campus_id === null ? null : (int) $package->campus_id,
            'service_package',
            (int) $package->id,
            (int) $package->version_no,
            $package->toArray()
        );

        return [
            'service_package_id' => (int) $package->id,
            'version_no' => (int) $package->version_no,
            'status' => $this->statusValue($package->status),
        ];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        return $this->packages->page($tenantId, $filters, $page, $pageSize);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}

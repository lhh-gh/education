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

namespace App\Repository\Education\Standards;

use App\Model\Education\Standards\EducationCourseServicePackage;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class ServicePackageRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data, ?EducationUserContext $context = null): EducationCourseServicePackage
    {
        if (isset($data['id'])) {
            $package = $context === null
                ? EducationCourseServicePackage::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id'])
                : $this->findInContext($context, (int) $data['id']);
            if ($context !== null) {
                $data = array_merge($data, [
                    'tenant_id' => (int) $package->tenant_id,
                    'campus_id' => $package->campus_id === null ? null : (int) $package->campus_id,
                ]);
            }
            $package->fill($data);
            $package->save();

            return $package;
        }

        if ($context !== null) {
            $data = array_merge($data, [
                'tenant_id' => $this->tenantId($context, $data),
                'campus_id' => $context->currentCampusId,
            ]);
        }

        return EducationCourseServicePackage::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationCourseServicePackage
    {
        return EducationCourseServicePackage::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function findInContext(EducationUserContext $context, int $id): EducationCourseServicePackage
    {
        return (new EducationScopeQuery())
            ->applyTenantCampus(EducationCourseServicePackage::query(), [], $context)
            ->findOrFail($id);
    }

    public function nextVersionNo(int $tenantId, string $packageCode): int
    {
        $latest = EducationCourseServicePackage::query()
            ->where('tenant_id', $tenantId)
            ->where('package_code', $packageCode)
            ->max('version_no');

        return ((int) $latest) + 1;
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $query = EducationCourseServicePackage::query()->where('tenant_id', $tenantId);
        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }
        $total = (int) $query->count();

        return [
            'list' => $query->orderByDesc('id')->forPage($page, $pageSize)->get()->toArray(),
            'total' => $total,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tenantId(EducationUserContext $context, array $data): int
    {
        if ($context->tenantId !== null) {
            return $context->tenantId;
        }
        if (isset($data['tenant_id']) && $data['tenant_id'] !== '') {
            return (int) $data['tenant_id'];
        }

        throw new \RuntimeException('education tenant context is missing', 403);
    }
}

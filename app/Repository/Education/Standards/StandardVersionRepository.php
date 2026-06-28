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

use App\Model\Education\Standards\EducationCourseLocalizationOverride;
use App\Model\Education\Standards\EducationCourseStandardPublishLog;
use App\Model\Education\Standards\EducationCourseStandardReviewRecord;
use App\Model\Education\Standards\EducationCourseStandardVersion;

final class StandardVersionRepository
{
    /**
     * @param array<string, mixed> $snapshot
     */
    public function saveSnapshot(int $tenantId, ?int $campusId, string $businessType, int $businessId, int $versionNo, array $snapshot): EducationCourseStandardVersion
    {
        return EducationCourseStandardVersion::query()->updateOrCreate([
            'tenant_id' => $tenantId,
            'business_type' => $businessType,
            'business_id' => $businessId,
            'version_no' => $versionNo,
        ], [
            'campus_id' => $campusId,
            'status' => 'draft',
            'snapshot_json' => $snapshot,
        ]);
    }

    public function findInTenant(int $tenantId, int $id): EducationCourseStandardVersion
    {
        return EducationCourseStandardVersion::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function findInCampus(int $tenantId, int $campusId, int $id): EducationCourseStandardVersion
    {
        return EducationCourseStandardVersion::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->findOrFail($id);
    }

    public function hasApprovedReview(EducationCourseStandardVersion $version): bool
    {
        $query = EducationCourseStandardReviewRecord::query()
            ->where('tenant_id', $version->tenant_id)
            ->where('standard_version_id', $version->id)
            ->where('status', 'approved');
        if ($version->campus_id === null) {
            $query->whereNull('campus_id');
        } else {
            $query->where('campus_id', (int) $version->campus_id);
        }

        return $query->exists();
    }

    public function publish(EducationCourseStandardVersion $version, int $operatorId): void
    {
        $fromStatus = $this->statusValue($version->status);
        $version->status = 'published';
        $version->published_by = $operatorId;
        $version->published_at = date('Y-m-d H:i:s');
        $version->save();

        EducationCourseStandardPublishLog::query()->create([
            'tenant_id' => $version->tenant_id,
            'campus_id' => $version->campus_id,
            'standard_version_id' => $version->id,
            'business_type' => $version->business_type,
            'business_id' => $version->business_id,
            'from_status' => $fromStatus,
            'to_status' => 'published',
            'operator_id' => $operatorId,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveLocalizationOverride(array $data): EducationCourseLocalizationOverride
    {
        return EducationCourseLocalizationOverride::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'],
            'standard_version_id' => $data['standard_version_id'],
        ], $data + ['status' => 'draft']);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}

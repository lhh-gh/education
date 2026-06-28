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

use App\Model\Education\Standards\EducationTrialLessonStandard;
use App\Model\Education\Standards\EducationTrialLessonStandardItem;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

final class TrialStandardRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data, ?EducationUserContext $context = null): EducationTrialLessonStandard
    {
        $standard = EducationTrialLessonStandard::query()
            ->where('tenant_id', $data['tenant_id'])
            ->where('standard_code', $data['standard_code'])
            ->where('version_no', $data['version_no'] ?? 1)
            ->first();

        if ($standard !== null) {
            if ($context !== null) {
                $standard = (new EducationScopeQuery())
                    ->applyTenantCampus(EducationTrialLessonStandard::query(), [], $context)
                    ->findOrFail((int) $standard->id);
                $data = array_merge($data, [
                    'tenant_id' => (int) $standard->tenant_id,
                    'campus_id' => $standard->campus_id === null ? null : (int) $standard->campus_id,
                ]);
            }
            $standard->fill($data + ['version_no' => 1, 'status' => 'draft']);
            $standard->save();

            return $standard;
        }

        if ($context !== null) {
            $data = array_merge($data, [
                'tenant_id' => $this->tenantId($context, $data),
                'campus_id' => $context->currentCampusId,
            ]);
        }

        return EducationTrialLessonStandard::query()->create($data + ['version_no' => 1, 'status' => 'draft']);
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function replaceItems(int $tenantId, int $campusId, int $standardId, array $items): void
    {
        EducationTrialLessonStandardItem::query()
            ->where('tenant_id', $tenantId)
            ->where('trial_lesson_standard_id', $standardId)
            ->delete();

        foreach ($items as $item) {
            EducationTrialLessonStandardItem::query()->create($item + [
                'tenant_id' => $tenantId,
                'campus_id' => $campusId,
                'trial_lesson_standard_id' => $standardId,
            ]);
        }
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

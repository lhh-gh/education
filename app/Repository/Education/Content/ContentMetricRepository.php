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

namespace App\Repository\Education\Content;

use App\Model\Education\Content\EducationMaterialReadRecord;
use App\Model\Education\Content\EducationMaterialUsageMetricDaily;
use App\Model\Education\Content\EducationShowcaseReadRecord;
use App\Model\Education\Content\EducationStudentWorkMetricDaily;

final class ContentMetricRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveMaterialDaily(array $data): EducationMaterialUsageMetricDaily
    {
        return EducationMaterialUsageMetricDaily::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'metric_date' => $data['metric_date'],
            'material_id' => $data['material_id'] ?? null,
        ], $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveStudentWorkDaily(array $data): EducationStudentWorkMetricDaily
    {
        return EducationStudentWorkMetricDaily::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'] ?? null,
            'metric_date' => $data['metric_date'],
            'student_id' => $data['student_id'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
        ], $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveMaterialRead(array $data): EducationMaterialReadRecord
    {
        return EducationMaterialReadRecord::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'material_version_id' => $data['material_version_id'],
            'student_id' => $data['student_id'] ?? null,
            'guardian_user_id' => $data['guardian_user_id'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
        ], $data + ['read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveShowcaseRead(array $data): EducationShowcaseReadRecord
    {
        return EducationShowcaseReadRecord::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'showcase_id' => $data['showcase_id'],
            'guardian_user_id' => $data['guardian_user_id'],
        ], $data + ['read_at' => date('Y-m-d H:i:s')]);
    }
}

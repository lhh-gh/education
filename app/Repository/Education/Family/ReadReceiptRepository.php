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

namespace App\Repository\Education\Family;

use App\Model\Education\Family\EducationFamilyReadReceipt;
use Carbon\Carbon;

final class ReadReceiptRepository
{
    public function createOnce(
        int $tenantId,
        ?int $campusId,
        string $businessType,
        int $businessId,
        int $studentId,
        string $readerType,
        int $readerUserId
    ): EducationFamilyReadReceipt {
        $receipt = EducationFamilyReadReceipt::query()
            ->where('tenant_id', $tenantId)
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->where('reader_type', $readerType)
            ->where('reader_user_id', $readerUserId)
            ->first();

        if ($receipt instanceof EducationFamilyReadReceipt) {
            return $receipt;
        }

        return EducationFamilyReadReceipt::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'business_type' => $businessType,
            'business_id' => $businessId,
            'student_id' => $studentId,
            'reader_type' => $readerType,
            'reader_user_id' => $readerUserId,
            'read_at' => Carbon::now(),
            'created_by' => $readerUserId,
            'updated_by' => $readerUserId,
        ]);
    }
}

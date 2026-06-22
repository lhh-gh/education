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

use App\Model\Education\Standards\EducationCourseStandardReviewRecord;

final class StandardReviewService
{
    /**
     * @return array{review_record_id: int, status: string}
     */
    public function review(int $tenantId, int $campusId, int $reviewRecordId, int $reviewerId, string $status, ?string $note = null): array
    {
        $record = EducationCourseStandardReviewRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->findOrFail($reviewRecordId);
        if ((int) $record->reviewer_id !== $reviewerId) {
            throw new \RuntimeException('review record is assigned to another reviewer', 403);
        }
        $record->status = $status;
        $record->review_note = $note;
        $record->reviewed_at = date('Y-m-d H:i:s');
        $record->save();

        return ['review_record_id' => (int) $record->id, 'status' => $status];
    }
}

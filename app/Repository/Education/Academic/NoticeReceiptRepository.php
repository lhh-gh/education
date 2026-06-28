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

namespace App\Repository\Education\Academic;

use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Enums\Education\Academic\NoticeReceiptStatus;
use App\Model\Enums\Education\Academic\NoticeTargetType;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

/**
 * @extends IRepository<EducationNoticeReceipt>
 */
final class NoticeReceiptRepository extends IRepository
{
    public function __construct(
        protected readonly EducationNoticeReceipt $model
    ) {}

    public function buildPublishTargets(EducationNotice $notice, EducationUserContext $context): array
    {
        $query = EducationStudentGuardian::query()
            ->join('edu_students', 'edu_students.id', '=', 'edu_student_guardians.student_id')
            ->join('edu_guardians', 'edu_guardians.id', '=', 'edu_student_guardians.guardian_id')
            ->where('edu_student_guardians.tenant_id', (int) $notice->tenant_id)
            ->where('edu_students.tenant_id', (int) $notice->tenant_id)
            ->where('edu_guardians.tenant_id', (int) $notice->tenant_id)
            ->where('edu_student_guardians.can_receive_notice', true)
            ->where('edu_students.status', 'enabled')
            ->where('edu_guardians.status', 'enabled')
            ->whereNull('edu_students.deleted_at')
            ->whereNull('edu_guardians.deleted_at');

        if ($notice->target_type === NoticeTargetType::Campus->value) {
            $query->where('edu_students.campus_id', (int) ($notice->target_id ?? $notice->campus_id));
        }
        if ($notice->target_type === NoticeTargetType::Student->value) {
            $query->where('edu_student_guardians.student_id', (int) $notice->target_id);
        }
        if ($notice->target_type === NoticeTargetType::ClassTarget->value) {
            $query->join('edu_class_students', 'edu_class_students.student_id', '=', 'edu_student_guardians.student_id')
                ->where('edu_class_students.tenant_id', (int) $notice->tenant_id)
                ->where('edu_class_students.class_id', (int) $notice->target_id)
                ->where('edu_class_students.status', 'active')
                ->whereNull('edu_class_students.deleted_at');
        }

        if (! $context->platformAccess && $context->campusIds !== [] && $notice->target_type !== NoticeTargetType::All->value) {
            $query->whereIn('edu_students.campus_id', $context->campusIds);
        }

        return $query->select([
            'edu_student_guardians.guardian_id',
            'edu_student_guardians.student_id',
            'edu_student_guardians.relation',
            'edu_students.campus_id',
            'edu_students.name as student_name_snapshot',
            'edu_guardians.name as guardian_name_snapshot',
        ])
            ->distinct()
            ->get()
            ->map(static fn (mixed $row): array => [
                'guardian_id' => (int) $row->guardian_id,
                'student_id' => (int) $row->student_id,
                'campus_id' => (int) $row->campus_id,
                'relation' => $row->relation === null ? null : (string) $row->relation,
                'guardian_name_snapshot' => (string) $row->guardian_name_snapshot,
                'student_name_snapshot' => (string) $row->student_name_snapshot,
            ])
            ->values()
            ->all();
    }

    public function createReceipts(EducationNotice $notice, array $targets): int
    {
        $inserted = 0;
        $now = Carbon::now()->toDateTimeString();
        foreach ($targets as $target) {
            $exists = $this->getQuery()
                ->where('tenant_id', (int) $notice->tenant_id)
                ->where('notice_id', (int) $notice->id)
                ->where('guardian_id', (int) $target['guardian_id'])
                ->where('student_id', (int) $target['student_id'])
                ->exists();
            if ($exists) {
                continue;
            }

            $this->create([
                'tenant_id' => (int) $notice->tenant_id,
                'campus_id' => (int) $target['campus_id'],
                'notice_id' => (int) $notice->id,
                'guardian_id' => (int) $target['guardian_id'],
                'student_id' => (int) $target['student_id'],
                'relation' => $target['relation'] ?? null,
                'guardian_name_snapshot' => (string) $target['guardian_name_snapshot'],
                'student_name_snapshot' => (string) $target['student_name_snapshot'],
                'status' => NoticeReceiptStatus::Unread->value,
                'delivered_at' => $now,
            ]);
            ++$inserted;
        }

        return $inserted;
    }

    public function pageAdminReceipts(int $noticeId, array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->getQuery()
            ->join('edu_notices', 'edu_notices.id', '=', 'edu_notice_receipts.notice_id')
            ->where('edu_notice_receipts.notice_id', $noticeId)
            ->whereNull('edu_notices.deleted_at')
            ->select('edu_notice_receipts.*');
        $this->applyContext($query, $context, $params);
        $this->applyReceiptFilters($query, $params);
        $query->orderByDesc('edu_notice_receipts.id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function pageGuardianReceipts(int $tenantId, int $guardianId, array $params, int $page, int $pageSize): array
    {
        $query = $this->getQuery()
            ->join('edu_notices', 'edu_notices.id', '=', 'edu_notice_receipts.notice_id')
            ->where('edu_notice_receipts.tenant_id', $tenantId)
            ->where('edu_notice_receipts.guardian_id', $guardianId)
            ->where('edu_notices.status', '<>', 'withdrawn')
            ->whereNull('edu_notices.deleted_at')
            ->select('edu_notice_receipts.*', 'edu_notices.title', 'edu_notices.content', 'edu_notices.notice_type', 'edu_notices.priority', 'edu_notices.published_at', 'edu_notices.status as notice_status');
        $this->applyReceiptFilters($query, $params);
        if (isset($params['notice_type']) && $params['notice_type'] !== '') {
            $query->where('edu_notices.notice_type', (string) $params['notice_type']);
        }
        $query->orderByDesc('edu_notices.published_at')->orderByDesc('edu_notice_receipts.id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findGuardianReceipt(int $tenantId, int $guardianId, int $receiptId): ?EducationNoticeReceipt
    {
        $row = $this->getQuery()
            ->with('notice')
            ->where('tenant_id', $tenantId)
            ->where('guardian_id', $guardianId)
            ->whereKey($receiptId)
            ->first();

        return $row instanceof EducationNoticeReceipt ? $row : null;
    }

    public function markRead(int $receiptId, int $profileId): EducationNoticeReceipt
    {
        return Db::transaction(function () use ($receiptId, $profileId): EducationNoticeReceipt {
            $receipt = $this->getQuery()->whereKey($receiptId)->lockForUpdate()->first();
            if (! $receipt instanceof EducationNoticeReceipt) {
                throw new \RuntimeException('Notice receipt not found.');
            }
            if ($receipt->status === NoticeReceiptStatus::Read->value) {
                return $receipt->refresh();
            }

            $receipt->fill([
                'status' => NoticeReceiptStatus::Read->value,
                'read_at' => Carbon::now()->toDateTimeString(),
                'read_by_profile_id' => $profileId,
            ]);
            $receipt->save();
            EducationNotice::query()
                ->whereKey((int) $receipt->notice_id)
                ->increment('read_count');

            return $receipt->refresh();
        });
    }

    private function applyContext(mixed $query, EducationUserContext $context, array $params): void
    {
        (new EducationScopeQuery())->applyTenantCampusColumns(
            $query,
            $params,
            $context,
            'edu_notice_receipts.tenant_id',
            'edu_notice_receipts.campus_id'
        );
    }

    private function applyReceiptFilters(mixed $query, array $params): void
    {
        foreach (['student_id', 'guardian_id'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where('edu_notice_receipts.' . $column, (int) $params[$column]);
            }
        }
        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== 'all') {
            $query->where('edu_notice_receipts.status', (string) $params['status']);
        }
        if (isset($params['keyword']) && trim((string) $params['keyword']) !== '') {
            $keyword = '%' . trim((string) $params['keyword']) . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('edu_notice_receipts.guardian_name_snapshot', 'like', $keyword)
                    ->orWhere('edu_notice_receipts.student_name_snapshot', 'like', $keyword);
            });
        }
    }
}

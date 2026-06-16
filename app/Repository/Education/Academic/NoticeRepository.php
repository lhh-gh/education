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
use App\Model\Enums\Education\Academic\NoticeStatus;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationNotice>
 */
final class NoticeRepository extends IRepository
{
    public function __construct(
        protected readonly EducationNotice $model
    ) {}

    public function pageAdmin(array $params, int $page, int $pageSize, EducationUserContext $context): array
    {
        $query = $this->applyContext($this->getQuery(), $context, $params);
        $this->applyFilters($query, $params);
        $query->orderByDesc('id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findAdminVisible(int $id, EducationUserContext $context): ?EducationNotice
    {
        $row = $this->applyContext($this->getQuery(), $context, [])
            ->whereKey($id)
            ->first();

        return $row instanceof EducationNotice ? $row : null;
    }

    public function createDraft(array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
    {
        $tenantId = $this->tenantId($data, $context);

        return $this->create(array_merge($data, [
            'tenant_id' => $tenantId,
            'notice_no' => $this->nextNoticeNo($tenantId),
            'status' => NoticeStatus::Draft->value,
            'receipt_count' => 0,
            'read_count' => 0,
            'created_by' => $operatorId,
            'updated_by' => $operatorId,
        ]));
    }

    public function updateDraft(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
    {
        $notice = $this->findAdminVisible($id, $context);
        if (! $notice instanceof EducationNotice) {
            throw new \RuntimeException('Notice not found.');
        }
        $notice->fill(array_merge($data, ['updated_by' => $operatorId]));
        $notice->save();

        return $notice->refresh();
    }

    public function markPublished(int $id, int $receiptCount, ?int $operatorId, ?string $publishedAt = null): EducationNotice
    {
        $notice = $this->getQuery()->whereKey($id)->first();
        if (! $notice instanceof EducationNotice) {
            throw new \RuntimeException('Notice not found.');
        }
        $notice->fill([
            'status' => NoticeStatus::Published->value,
            'published_at' => $publishedAt ?? Carbon::now()->toDateTimeString(),
            'published_by' => $operatorId,
            'receipt_count' => $receiptCount,
            'updated_by' => $operatorId,
        ]);
        $notice->save();

        return $notice->refresh();
    }

    public function markWithdrawn(int $id, string $reason, ?int $operatorId): EducationNotice
    {
        $notice = $this->getQuery()->whereKey($id)->first();
        if (! $notice instanceof EducationNotice) {
            throw new \RuntimeException('Notice not found.');
        }
        $notice->fill([
            'status' => NoticeStatus::Withdrawn->value,
            'withdrawn_at' => Carbon::now()->toDateTimeString(),
            'withdrawn_by' => $operatorId,
            'withdraw_reason' => $reason,
            'updated_by' => $operatorId,
        ]);
        $notice->save();

        return $notice->refresh();
    }

    public function nextNoticeNo(int $tenantId): string
    {
        return 'NOT' . date('YmdHis') . str_pad((string) $tenantId, 4, '0', \STR_PAD_LEFT) . random_int(1000, 9999);
    }

    private function tenantId(array $data, EducationUserContext $context): int
    {
        return $context->tenantId ?? (int) ($data['tenant_id'] ?? 0);
    }

    private function applyContext(Builder $query, EducationUserContext $context, array $filters): Builder
    {
        if ($context->platformAccess) {
            if (isset($filters['tenant_id']) && $filters['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $filters['tenant_id']);
            }
            if (isset($filters['campus_id']) && $filters['campus_id'] !== '') {
                $query->where('campus_id', (int) $filters['campus_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->where('tenant_id', $context->tenantId);
        $campusId = isset($filters['campus_id']) && $filters['campus_id'] !== '' ? (int) $filters['campus_id'] : null;
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            if ($campusId !== null) {
                $query->where('campus_id', $campusId);
            }

            return $query;
        }

        if ($campusId !== null) {
            return $context->canAccessCampus($campusId)
                ? $query->where('campus_id', $campusId)
                : $this->emptyQuery($query);
        }

        if ($context->campusIds === []) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->where(static function (Builder $query) use ($context): void {
            $query->whereIn('campus_id', $context->campusIds)
                ->orWhereNull('campus_id');
        });

        return $query;
    }

    private function emptyQuery(Builder $query): Builder
    {
        $query->whereRaw('1 = 0');

        return $query;
    }

    private function applyFilters(Builder $query, array $params): void
    {
        foreach (['campus_id', 'target_id'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where($column, (int) $params[$column]);
            }
        }
        foreach (['notice_type', 'target_type', 'status', 'priority'] as $column) {
            if (isset($params[$column]) && $params[$column] !== '') {
                $query->where($column, (string) $params[$column]);
            }
        }
        if (isset($params['keyword']) && trim((string) $params['keyword']) !== '') {
            $keyword = '%' . trim((string) $params['keyword']) . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('notice_no', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword);
            });
        }
    }
}

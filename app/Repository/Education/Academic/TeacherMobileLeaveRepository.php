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

use App\Model\Education\Academic\EducationLeaveRequest;
use App\Model\Education\Academic\EducationLesson;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

/**
 * @extends IRepository<EducationLeaveRequest>
 */
final class TeacherMobileLeaveRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLeaveRequest $model
    ) {}

    public function pageAssignedLeave(array $params, int $page, int $pageSize, EducationUserContext $context, int $teacherId): array
    {
        $query = $this->assignedLeaveQuery($context, $teacherId, $params);
        $this->applyFilters($query, $params);
        $query->orderByDesc('edu_leave_requests.requested_at')->orderByDesc('edu_leave_requests.id');

        return $this->handlePage($query->paginate(
            perPage: $pageSize,
            pageName: self::PER_PAGE_PARAM_NAME,
            page: $page
        ));
    }

    public function findAssignedLeave(int $id, EducationUserContext $context, int $teacherId, ?int $campusId): ?EducationLeaveRequest
    {
        $row = $this->assignedLeaveQuery($context, $teacherId, ['campus_id' => $campusId])
            ->where('edu_leave_requests.id', $id)
            ->first();

        return $row instanceof EducationLeaveRequest ? $row : null;
    }

    public function updateReview(int $id, string $status, string $reviewRemark, ?int $operatorId): EducationLeaveRequest
    {
        return Db::transaction(static function () use ($id, $status, $reviewRemark, $operatorId): EducationLeaveRequest {
            $leave = EducationLeaveRequest::query()->whereKey($id)->lockForUpdate()->first();
            if (! $leave instanceof EducationLeaveRequest) {
                throw new \RuntimeException('Leave request not found.');
            }

            $leave->fill([
                'status' => $status,
                'reviewed_at' => Carbon::now()->toDateTimeString(),
                'reviewed_by' => $operatorId,
                'review_remark' => $reviewRemark,
                'updated_by' => $operatorId,
            ]);
            $leave->save();

            return $leave->refresh();
        });
    }

    private function assignedLeaveQuery(EducationUserContext $context, int $teacherId, array $params): Builder
    {
        $query = $this->getQuery();

        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->where('edu_leave_requests.tenant_id', $context->tenantId);
        $lessonQuery = EducationLesson::query()
            ->select('id')
            ->where('tenant_id', $context->tenantId)
            ->where('teacher_id', $teacherId);

        $campusId = \array_key_exists('campus_id', $params) && $params['campus_id'] !== null && $params['campus_id'] !== ''
            ? (int) $params['campus_id']
            : null;

        if ($campusId !== null) {
            $context->canAccessCampus($campusId)
                ? $lessonQuery->where('campus_id', $campusId)
                : $lessonQuery->whereRaw('1 = 0');

            $query->whereIn('edu_leave_requests.lesson_id', $lessonQuery);

            return $query;
        }

        $context->campusIds === []
            ? $lessonQuery->whereRaw('1 = 0')
            : $lessonQuery->whereIn('campus_id', $context->campusIds);

        $query->whereIn('edu_leave_requests.lesson_id', $lessonQuery);

        return $query;
    }

    private function applyFilters(Builder $query, array $params): void
    {
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('edu_leave_requests.status', (string) $params['status']);
        }
        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->whereHas('lesson', static function (Builder $query) use ($params): void {
                $query->where('start_at', '>=', (string) $params['start_at']);
            });
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->whereHas('lesson', static function (Builder $query) use ($params): void {
                $query->where('start_at', '<=', (string) $params['end_at']);
            });
        }
        if (isset($params['keyword']) && $params['keyword'] !== '') {
            $keyword = '%' . $params['keyword'] . '%';
            $query->where(static function (Builder $query) use ($keyword): void {
                $query->where('edu_leave_requests.leave_no', 'like', $keyword)
                    ->orWhere('edu_leave_requests.reason', 'like', $keyword);
            });
        }
    }
}

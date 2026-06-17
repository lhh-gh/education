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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationLessonChangeLog;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;

final class LessonChangeRepository
{
    public function pageByCampusScope(array $params, EducationUserContext $context): array
    {
        $query = EducationLessonChangeRequest::query()->where('tenant_id', $context->tenantId);
        if ($context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->platformAccess) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }
        foreach (['campus_id', 'status', 'change_type'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }

        return $this->paginate($query->orderByDesc('id'), (int) ($params['page'] ?? 1), (int) ($params['pageSize'] ?? 20));
    }

    public function findPendingByLesson(int $tenantId, int $lessonId): ?EducationLessonChangeRequest
    {
        return EducationLessonChangeRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->where('status', 'pending')
            ->first();
    }

    public function lockById(int $tenantId, int $id): ?EducationLessonChangeRequest
    {
        return EducationLessonChangeRequest::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($id)
            ->lockForUpdate()
            ->first();
    }

    public function createRequest(array $data): EducationLessonChangeRequest
    {
        return EducationLessonChangeRequest::query()->create($data);
    }

    public function writeLog(array $data): EducationLessonChangeLog
    {
        return EducationLessonChangeLog::query()->create($data);
    }

    private function paginate(mixed $query, int $page, int $pageSize): array
    {
        $page = max(1, $page);
        $pageSize = max(1, min(100, $pageSize));
        $total = (clone $query)->count();
        $list = $query->forPage($page, $pageSize)->get()->map(static fn ($row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}

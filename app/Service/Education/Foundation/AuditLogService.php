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

namespace App\Service\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Repository\Education\Foundation\AuditLogRepository;
use Carbon\CarbonInterface;

final class AuditLogService
{
    public function __construct(
        private readonly AuditLogRepository $repository
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        $page = $this->repository->pageByContext($filters, $context);

        return [
            'list' => array_map(fn (EducationAuditLog $log): array => $this->mapPageItem($log), $page['list']),
            'total' => $page['total'],
        ];
    }

    public function detail(int $id, EducationUserContext $context): array
    {
        $log = $this->repository->findVisibleById($id, $context);
        if (! $log instanceof EducationAuditLog) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'audit log not found', ['id' => $id]);
        }

        return $this->mapDetail($log);
    }

    private function mapPageItem(EducationAuditLog $log): array
    {
        return [
            'id' => $log->id,
            'tenant_id' => $log->tenant_id,
            'campus_id' => $log->campus_id,
            'actor_user_id' => $log->actor_user_id,
            'actor_type' => $log->actor_type,
            'actor_role_code' => $log->actor_role_code,
            'module' => $log->module,
            'resource' => $log->resource,
            'action' => $log->action,
            'business_type' => $log->business_type,
            'business_id' => $log->business_id,
            'request_id' => $log->request_id,
            'ip_address' => $log->ip_address,
            'method' => $log->method,
            'path' => $log->path,
            'summary' => $log->summary,
            'created_at' => $this->formatDate($log->created_at),
        ];
    }

    private function mapDetail(EducationAuditLog $log): array
    {
        return $this->mapPageItem($log) + [
            'user_agent' => $log->user_agent,
            'before_snapshot' => $log->before_snapshot,
            'after_snapshot' => $log->after_snapshot,
            'diff' => $log->diff,
            'metadata' => $log->metadata,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        }

        return $value === null ? null : (string) $value;
    }
}

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

namespace App\Schema\Education\Foundation;

use App\Model\Education\Foundation\EducationAuditLog;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationAuditLogSchema')]
final class AuditLogSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    #[Property(property: 'tenant_id', title: 'Tenant ID', type: 'int', nullable: true)]
    public ?int $tenantId;

    #[Property(property: 'campus_id', title: 'Campus ID', type: 'int', nullable: true)]
    public ?int $campusId;

    #[Property(property: 'actor_user_id', title: 'Actor user ID', type: 'int', nullable: true)]
    public ?int $actorUserId;

    #[Property(property: 'actor_type', title: 'Actor type', type: 'string')]
    public ?string $actorType;

    #[Property(property: 'actor_role_code', title: 'Actor role code', type: 'string', nullable: true)]
    public ?string $actorRoleCode;

    #[Property(property: 'module', title: 'Module', type: 'string')]
    public ?string $module;

    #[Property(property: 'resource', title: 'Resource', type: 'string')]
    public ?string $resource;

    #[Property(property: 'action', title: 'Action', type: 'string')]
    public ?string $action;

    #[Property(property: 'business_type', title: 'Business type', type: 'string')]
    public ?string $businessType;

    #[Property(property: 'business_id', title: 'Business ID', type: 'string', nullable: true)]
    public ?string $businessId;

    #[Property(property: 'request_id', title: 'Request ID', type: 'string', nullable: true)]
    public ?string $requestId;

    #[Property(property: 'ip_address', title: 'IP address', type: 'string', nullable: true)]
    public ?string $ipAddress;

    #[Property(property: 'user_agent', title: 'User agent', type: 'string', nullable: true)]
    public ?string $userAgent;

    #[Property(property: 'method', title: 'HTTP method', type: 'string', nullable: true)]
    public ?string $method;

    #[Property(property: 'path', title: 'HTTP path', type: 'string', nullable: true)]
    public ?string $path;

    #[Property(property: 'summary', title: 'Summary', type: 'string', nullable: true)]
    public ?string $summary;

    #[Property(property: 'before_snapshot', title: 'Before snapshot', type: 'object', nullable: true)]
    public ?array $beforeSnapshot;

    #[Property(property: 'after_snapshot', title: 'After snapshot', type: 'object', nullable: true)]
    public ?array $afterSnapshot;

    #[Property(property: 'diff', title: 'Diff', type: 'object', nullable: true)]
    public ?array $diff;

    #[Property(property: 'metadata', title: 'Metadata', type: 'object', nullable: true)]
    public ?array $metadata;

    #[Property(property: 'created_at', title: 'Created at', type: 'string', nullable: true)]
    public ?string $createdAt;

    public function __construct(EducationAuditLog $model)
    {
        $this->id = $model->id;
        $this->tenantId = $model->tenant_id;
        $this->campusId = $model->campus_id;
        $this->actorUserId = $model->actor_user_id;
        $this->actorType = $model->actor_type;
        $this->actorRoleCode = $model->actor_role_code;
        $this->module = $model->module;
        $this->resource = $model->resource;
        $this->action = $model->action;
        $this->businessType = $model->business_type;
        $this->businessId = $model->business_id;
        $this->requestId = $model->request_id;
        $this->ipAddress = $model->ip_address;
        $this->userAgent = $model->user_agent;
        $this->method = $model->method;
        $this->path = $model->path;
        $this->summary = $model->summary;
        $this->beforeSnapshot = $model->before_snapshot;
        $this->afterSnapshot = $model->after_snapshot;
        $this->diff = $model->diff;
        $this->metadata = $model->metadata;
        $this->createdAt = $this->formatDate($model->created_at);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'campus_id' => $this->campusId,
            'actor_user_id' => $this->actorUserId,
            'actor_type' => $this->actorType,
            'actor_role_code' => $this->actorRoleCode,
            'module' => $this->module,
            'resource' => $this->resource,
            'action' => $this->action,
            'business_type' => $this->businessType,
            'business_id' => $this->businessId,
            'request_id' => $this->requestId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'method' => $this->method,
            'path' => $this->path,
            'summary' => $this->summary,
            'before_snapshot' => $this->beforeSnapshot,
            'after_snapshot' => $this->afterSnapshot,
            'diff' => $this->diff,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt,
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

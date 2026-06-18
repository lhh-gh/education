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

use App\Contract\Education\Foundation\AuditLoggerInterface;
use App\Event\Education\Foundation\EducationAuditEvent;
use App\Model\Education\Foundation\EducationAuditLog;

final class AuditLogger implements AuditLoggerInterface
{
    private const DROP_KEYS = [
        'password',
        'password_hash',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'secret',
        'openid',
        'unionid',
        'id_card',
        'bank_card',
    ];

    private const MASK_KEYS = [
        'phone',
        'mobile',
        'email',
    ];

    public function __construct(
        private readonly AuditContextResolver $contextResolver
    ) {}

    public function record(EducationAuditEvent $event): EducationAuditLog
    {
        /* @var EducationAuditLog $log */
        return EducationAuditLog::query()->create([
            'tenant_id' => $this->contextResolver->resolveTenantId($event->context),
            'campus_id' => $this->contextResolver->resolveCampusId($event->context, $event->metadata),
            'actor_user_id' => $event->context?->userId,
            'actor_type' => $event->actorType,
            'actor_role_code' => $this->contextResolver->resolveActorRoleCode($event->context),
            'module' => $event->module,
            'resource' => $event->resource,
            'action' => $event->action,
            'business_type' => $event->businessType,
            'business_id' => $event->businessId === null ? null : (string) $event->businessId,
            'request_id' => $this->contextResolver->resolveRequestId(),
            'ip_address' => $this->contextResolver->resolveIpAddress(),
            'user_agent' => $this->contextResolver->resolveUserAgent(),
            'method' => $this->contextResolver->resolveMethod(),
            'path' => $this->contextResolver->resolvePath(),
            'summary' => $event->summary,
            'before_snapshot' => $this->sanitize($event->beforeSnapshot),
            'after_snapshot' => $this->sanitize($event->afterSnapshot),
            'diff' => $this->buildDiff($event->beforeSnapshot, $event->afterSnapshot),
            'metadata' => $this->sanitize($event->metadata),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function sanitize(array $payload): array
    {
        $sanitized = [];
        foreach ($payload as $key => $value) {
            $normalizedKey = $this->normalizeKey((string) $key);
            if ($this->shouldDrop($normalizedKey)) {
                continue;
            }

            if ($this->shouldMask($normalizedKey)) {
                $sanitized[$key] = $this->maskValue($value, $normalizedKey);
                continue;
            }

            $sanitized[$key] = $this->sanitizeValue($value);
        }

        return $sanitized;
    }

    private function buildDiff(array $before, array $after): array
    {
        $safeBefore = $this->sanitize($before);
        $safeAfter = $this->sanitize($after);
        $diff = [];

        foreach (array_unique([...array_keys($safeBefore), ...array_keys($safeAfter)]) as $key) {
            $beforeValue = $safeBefore[$key] ?? null;
            $afterValue = $safeAfter[$key] ?? null;
            if ($beforeValue === $afterValue) {
                continue;
            }

            $diff[$key] = [
                'before' => $beforeValue,
                'after' => $afterValue,
            ];
        }

        return $diff;
    }

    private function truncateString(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (\is_array($value)) {
            return $this->sanitize($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \JsonSerializable) {
            $jsonValue = $value->jsonSerialize();

            return \is_array($jsonValue) ? $this->sanitize($jsonValue) : $this->sanitizeValue($jsonValue);
        }

        if ($value instanceof \Stringable) {
            return $this->truncateString((string) $value, 1000);
        }

        if (\is_string($value)) {
            return $this->truncateString($value, 1000);
        }

        if (\is_scalar($value) || $value === null) {
            return $value;
        }

        return null;
    }

    private function normalizeKey(string $key): string
    {
        return mb_strtolower(str_replace(['-', '.'], '_', $key));
    }

    private function shouldDrop(string $normalizedKey): bool
    {
        foreach (self::DROP_KEYS as $dropKey) {
            if (str_contains($normalizedKey, $dropKey)) {
                return true;
            }
        }

        return false;
    }

    private function shouldMask(string $normalizedKey): bool
    {
        foreach (self::MASK_KEYS as $maskKey) {
            if (str_contains($normalizedKey, $maskKey)) {
                return true;
            }
        }

        return false;
    }

    private function maskValue(mixed $value, string $normalizedKey): string
    {
        if (! \is_scalar($value) && ! $value instanceof \Stringable) {
            return '***';
        }

        $value = (string) $value;
        $value = $this->truncateString($value, 1000);
        if (str_contains($normalizedKey, 'email')) {
            if (! str_contains($value, '@')) {
                return '***';
            }

            [$name, $domain] = explode('@', $value, 2);
            $prefix = $name === '' ? '*' : mb_substr($name, 0, 1);

            return $prefix . '***@' . $domain;
        }

        $length = mb_strlen($value);
        if ($length <= 7) {
            return '***';
        }

        return mb_substr($value, 0, 3) . '****' . mb_substr($value, -4);
    }
}

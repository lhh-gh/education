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

namespace App\Service\Education\Academic;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationClass;
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Foundation\EducationCampus;
use App\Model\Enums\Education\Academic\NoticeStatus;
use App\Model\Enums\Education\Academic\NoticeTargetType;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\NoticeReceiptRepository;
use App\Repository\Education\Academic\NoticeRepository;
use App\Schema\Education\Academic\NoticeReceiptSchema;
use App\Schema\Education\Academic\NoticeSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class NoticeService
{
    public function __construct(
        private readonly NoticeRepository $repository,
        private readonly NoticeReceiptRepository $receiptRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function page(array $params, EducationUserContext $context): array
    {
        return $this->repository->pageAdmin(
            $params,
            max(1, (int) ($params['page'] ?? 1)),
            max(1, min(100, (int) ($params['pageSize'] ?? $params['page_size'] ?? 15))),
            $context
        );
    }

    public function detail(int $id, EducationUserContext $context): array
    {
        return (new NoticeSchema($this->findScoped($id, $context)))->jsonSerialize();
    }

    public function create(array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
    {
        $payload = $this->normalizePayload($data, $context);
        $notice = $this->repository->createDraft($payload, $context, $operatorId)->refresh();
        $this->dispatchAudit('created', $notice, $context, [], $notice->toArray());

        return $notice;
    }

    public function update(int $id, array $data, EducationUserContext $context, ?int $operatorId): EducationNotice
    {
        $notice = $this->findScoped($id, $context);
        $this->assertStatus($notice, NoticeStatus::Draft, 'only draft notice can be updated');
        $before = $notice->toArray();
        $updated = $this->repository->updateDraft($id, $this->normalizePayload($data, $context), $context, $operatorId);
        $this->dispatchAudit('updated', $updated, $context, $before, $updated->toArray());

        return $updated;
    }

    public function publish(int $id, array $payload, EducationUserContext $context, ?int $operatorId): array
    {
        $notice = $this->findScoped($id, $context);
        $this->assertStatus($notice, NoticeStatus::Draft, 'only draft notice can be published');

        return Db::transaction(function () use ($notice, $payload, $context, $operatorId): array {
            $targets = $this->receiptRepository->buildPublishTargets($notice, $context);
            if ($targets === []) {
                throw new BusinessException(ResultCode::CONFLICT, 'notice target has no receivable guardians', [
                    'target_type' => $notice->target_type,
                    'target_id' => $notice->target_id,
                ]);
            }

            $receiptCount = $this->receiptRepository->createReceipts($notice, $targets);
            $published = $this->repository->markPublished((int) $notice->id, $receiptCount, $operatorId, $payload['published_at'] ?? null);
            $this->dispatchAudit('published', $published, $context, $notice->toArray(), $published->toArray());

            return [
                'notice' => (new NoticeSchema($published))->jsonSerialize(),
                'receipt_count' => $receiptCount,
            ];
        });
    }

    public function withdraw(int $id, array $payload, EducationUserContext $context, ?int $operatorId): EducationNotice
    {
        $notice = $this->findScoped($id, $context);
        $this->assertStatus($notice, NoticeStatus::Published, 'only published notice can be withdrawn');
        $before = $notice->toArray();
        $withdrawn = $this->repository->markWithdrawn($id, trim((string) $payload['withdraw_reason']), $operatorId);
        $this->dispatchAudit('withdrawn', $withdrawn, $context, $before, $withdrawn->toArray());

        return $withdrawn;
    }

    public function receipts(int $id, array $params, EducationUserContext $context): array
    {
        $this->findScoped($id, $context);
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['pageSize'] ?? $params['page_size'] ?? 15)));
        $result = $this->receiptRepository->pageAdminReceipts($id, $params, $page, $pageSize, $context);
        $result['list'] = array_map(static fn (array $row): array => (new NoticeReceiptSchema($row))->jsonSerialize(), $result['list']);

        return $result;
    }

    private function findScoped(int $id, EducationUserContext $context): EducationNotice
    {
        $notice = $this->repository->findAdminVisible($id, $context);
        if (! $notice instanceof EducationNotice) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'notice not found in current context', ['id' => $id]);
        }

        return $notice;
    }

    private function assertStatus(EducationNotice $notice, NoticeStatus $status, string $message): void
    {
        if ($notice->status !== $status->value) {
            throw new BusinessException(ResultCode::CONFLICT, $message, ['id' => (int) $notice->id, 'status' => $notice->status]);
        }
    }

    private function normalizePayload(array $data, EducationUserContext $context): array
    {
        $targetType = (string) $data['target_type'];
        $payload = [
            'tenant_id' => $context->tenantId ?? (int) ($data['tenant_id'] ?? 0),
            'campus_id' => isset($data['campus_id']) && $data['campus_id'] !== '' ? (int) $data['campus_id'] : null,
            'notice_type' => (string) $data['notice_type'],
            'target_type' => $targetType,
            'target_id' => isset($data['target_id']) && $data['target_id'] !== '' ? (int) $data['target_id'] : null,
            'title' => trim((string) $data['title']),
            'content' => (string) $data['content'],
            'priority' => (string) $data['priority'],
            'expire_at' => $data['expire_at'] ?? null,
            'remark' => $data['remark'] ?? null,
        ];

        if ($payload['tenant_id'] <= 0) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant is outside current context');
        }

        if ($targetType === NoticeTargetType::All->value) {
            $payload['campus_id'] = null;
            $payload['target_id'] = null;

            return $payload;
        }

        if ($targetType === NoticeTargetType::Campus->value) {
            $campusId = (int) ($payload['target_id'] ?? $payload['campus_id'] ?? 0);
            $this->assertCampusVisible($campusId, (int) $payload['tenant_id'], $context);
            $payload['campus_id'] = $campusId;
            $payload['target_id'] = $campusId;

            return $payload;
        }

        if ($targetType === NoticeTargetType::ClassTarget->value) {
            $class = $this->classVisible((int) ($payload['target_id'] ?? 0), (int) $payload['tenant_id'], $context);
            $payload['campus_id'] = (int) $class->campus_id;

            return $payload;
        }

        if ($targetType === NoticeTargetType::Student->value) {
            $student = $this->studentVisible((int) ($payload['target_id'] ?? 0), (int) $payload['tenant_id'], $context);
            $payload['campus_id'] = (int) $student->campus_id;

            return $payload;
        }

        throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'target_type has an invalid value', ['field' => 'target_type']);
    }

    private function assertCampusVisible(int $campusId, int $tenantId, EducationUserContext $context): void
    {
        $campus = EducationCampus::query()
            ->whereKey($campusId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'enabled')
            ->first();
        if (! $campus instanceof EducationCampus || (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus($campusId))) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'campus is outside current scope', ['campus_id' => $campusId]);
        }
    }

    private function classVisible(int $classId, int $tenantId, EducationUserContext $context): EducationClass
    {
        $class = EducationClass::query()
            ->whereKey($classId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'enabled')
            ->first();
        if (! $class instanceof EducationClass) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'target class not found in current context', ['target_id' => $classId]);
        }
        $this->assertCampusVisible((int) $class->campus_id, $tenantId, $context);

        return $class;
    }

    private function studentVisible(int $studentId, int $tenantId, EducationUserContext $context): EducationStudent
    {
        $student = EducationStudent::query()
            ->whereKey($studentId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'enabled')
            ->first();
        if (! $student instanceof EducationStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'target student not found in current context', ['target_id' => $studentId]);
        }
        $this->assertCampusVisible((int) $student->campus_id, $tenantId, $context);

        return $student;
    }

    private function dispatchAudit(string $action, EducationNotice $notice, EducationUserContext $context, array $before, array $after): void
    {
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'notice',
            action: 'education.academic.notice.' . $action,
            businessType: 'notice',
            businessId: (int) $notice->id,
            context: $context,
            beforeSnapshot: $before,
            afterSnapshot: $after,
            metadata: ['tenant_id' => (int) $notice->tenant_id, 'campus_id' => $notice->campus_id === null ? null : (int) $notice->campus_id],
            summary: 'Notice ' . $action
        ));
    }
}

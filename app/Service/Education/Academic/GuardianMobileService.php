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
use App\Model\Education\Academic\EducationNotice;
use App\Model\Education\Academic\EducationNoticeReceipt;
use App\Repository\Education\Academic\GuardianMobileRepository;
use App\Repository\Education\Academic\NoticeReceiptRepository;
use App\Schema\Education\Academic\GuardianAccountSchema;
use App\Schema\Education\Academic\GuardianConsumptionSchema;
use App\Schema\Education\Academic\GuardianLessonSchema;
use App\Schema\Education\Academic\GuardianStudentSchema;
use App\Service\Education\Foundation\EducationUserContext;
use Psr\EventDispatcher\EventDispatcherInterface;

final class GuardianMobileService
{
    public function __construct(
        private readonly GuardianMobileContextResolver $contextResolver,
        private readonly GuardianMobileRepository $repository,
        private readonly NoticeReceiptRepository $noticeReceiptRepository,
        private readonly GuardianStudentSchema $studentSchema,
        private readonly GuardianLessonSchema $lessonSchema,
        private readonly GuardianAccountSchema $accountSchema,
        private readonly GuardianConsumptionSchema $consumptionSchema,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function students(array $params, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);

        return [
            'list' => array_map(fn (array $row): array => $this->studentSchema->student($row), $this->repository->listBoundStudents((int) $context->tenantId, (int) $guardian->id)),
            'total' => \count($this->repository->listBoundStudents((int) $context->tenantId, (int) $guardian->id)),
        ];
    }

    public function lessons(int $studentId, array $params, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $this->assertBoundStudent((int) $context->tenantId, (int) $guardian->id, $studentId);
        $result = $this->repository->pageStudentLessons((int) $context->tenantId, (int) $guardian->id, $studentId, $params, $this->page($params), $this->pageSize($params));
        $result['list'] = array_map(fn (array $row): array => $this->lessonSchema->lesson($row), $result['list']);

        return $result;
    }

    public function accounts(int $studentId, array $params, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $this->assertBoundStudent((int) $context->tenantId, (int) $guardian->id, $studentId);
        $result = $this->repository->pageStudentAccounts((int) $context->tenantId, (int) $guardian->id, $studentId, $params, $this->page($params), $this->pageSize($params));
        $result['list'] = array_map(fn (array $row): array => $this->accountSchema->account($row), $result['list']);

        return $result;
    }

    public function consumptions(int $studentId, array $params, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $this->assertBoundStudent((int) $context->tenantId, (int) $guardian->id, $studentId);
        $result = $this->repository->pageStudentConsumptions((int) $context->tenantId, (int) $guardian->id, $studentId, $params, $this->page($params), $this->pageSize($params));
        $result['list'] = array_map(fn (array $row): array => $this->consumptionSchema->consumption($row), $result['list']);

        return $result;
    }

    public function noticePage(array $params, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $result = $this->noticeReceiptRepository->pageGuardianReceipts((int) $context->tenantId, (int) $guardian->id, $params, $this->page($params), $this->pageSize($params));
        $result['list'] = array_map(fn (array $row): array => $this->noticeCard($row), $result['list']);

        return $result;
    }

    public function noticeDetail(int $receiptId, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $receipt = $this->guardianReceipt((int) $context->tenantId, (int) $guardian->id, $receiptId);

        return $this->noticeDetailPayload($receipt);
    }

    public function readNotice(int $receiptId, EducationUserContext $context): array
    {
        $guardian = $this->contextResolver->resolveGuardian($context);
        $receipt = $this->guardianReceipt((int) $context->tenantId, (int) $guardian->id, $receiptId);
        $read = $this->noticeReceiptRepository->markRead((int) $receipt->id, $this->contextResolver->currentOperatorId($context) ?? 0);
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'guardian_mobile_notice',
            action: 'education.academic.guardian_mobile.notice_read',
            businessType: 'notice_receipt',
            businessId: (int) $read->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['id' => (int) $read->id, 'status' => (string) $read->status],
            metadata: ['tenant_id' => (int) $read->tenant_id, 'campus_id' => (int) $read->campus_id],
            summary: 'Guardian mobile notice read',
            actorType: 'guardian'
        ));

        return [
            'receipt_id' => (int) $read->id,
            'status' => (string) $read->status,
            'read_at' => $read->read_at?->toDateTimeString(),
        ];
    }

    private function assertBoundStudent(int $tenantId, int $guardianId, int $studentId): array
    {
        $binding = $this->repository->assertBoundStudent($tenantId, $guardianId, $studentId);
        if ($binding === []) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }

        return $binding;
    }

    private function guardianReceipt(int $tenantId, int $guardianId, int $receiptId): EducationNoticeReceipt
    {
        $receipt = $this->noticeReceiptRepository->findGuardianReceipt($tenantId, $guardianId, $receiptId);
        if (! $receipt instanceof EducationNoticeReceipt || ! $receipt->notice instanceof EducationNotice) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'notice receipt not found in current guardian context', ['receipt_id' => $receiptId]);
        }

        return $receipt;
    }

    private function noticeCard(array $row): array
    {
        return [
            'receipt_id' => (int) $row['id'],
            'notice_id' => (int) $row['notice_id'],
            'title' => (string) $row['title'],
            'notice_type' => (string) $row['notice_type'],
            'priority' => (string) $row['priority'],
            'student_name_snapshot' => (string) $row['student_name_snapshot'],
            'status' => (string) $row['status'],
            'published_at' => isset($row['published_at']) ? (string) $row['published_at'] : null,
        ];
    }

    private function noticeDetailPayload(EducationNoticeReceipt $receipt): array
    {
        $notice = $receipt->notice;

        return [
            'receipt_id' => (int) $receipt->id,
            'notice_id' => (int) $receipt->notice_id,
            'title' => (string) $notice->title,
            'content' => (string) $notice->content,
            'notice_type' => (string) $notice->notice_type,
            'priority' => (string) $notice->priority,
            'student_name_snapshot' => (string) $receipt->student_name_snapshot,
            'status' => (string) $receipt->status,
            'published_at' => $notice->published_at?->toDateTimeString(),
            'read_at' => $receipt->read_at?->toDateTimeString(),
        ];
    }

    private function page(array $params): int
    {
        return max(1, (int) ($params['page'] ?? 1));
    }

    private function pageSize(array $params): int
    {
        return max(1, min(100, (int) ($params['pageSize'] ?? $params['page_size'] ?? 20)));
    }
}

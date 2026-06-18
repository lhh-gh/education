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

namespace App\Service\Education\Family;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Family\EducationGrowthRecord;
use App\Repository\Education\Family\GrowthRecordRepository;
use App\Repository\Education\Family\HomeworkRepository;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class GrowthRecordService
{
    public function __construct(
        private readonly GrowthRecordRepository $repository,
        private readonly HomeworkRepository $homeworkRepository,
        private readonly TeacherMobileContextResolver $teacherResolver,
        private readonly GuardianMobileContextResolver $guardianResolver
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{growth_record_id: int, status: string}
     */
    public function saveByTeacher(array $data, EducationUserContext $context): array
    {
        $teacher = $this->teacherResolver->resolveTeacher($context);
        $publish = (bool) ($data['publish'] ?? false);
        $record = $this->repository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => $context->currentCampusId,
            'student_id' => (int) ($data['student_id'] ?? 0),
            'teacher_id' => (int) $teacher->id,
            'record_type' => (string) ($data['record_type'] ?? 'daily'),
            'title' => (string) ($data['title'] ?? ''),
            'content' => (string) ($data['content'] ?? ''),
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? Carbon::now() : null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['growth_record_id' => (int) $record->id, 'status' => $this->statusValue($record)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function visibleForGuardian(int $studentId, EducationUserContext $context): array
    {
        $guardian = $this->guardianResolver->resolveGuardian($context);
        if (! $this->homeworkRepository->isGuardianBound((int) $context->tenantId, $studentId, (int) $guardian->id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }

        return $this->repository->publishedForStudent((int) $context->tenantId, $studentId);
    }

    private function statusValue(EducationGrowthRecord $record): string
    {
        return $record->status instanceof \BackedEnum ? (string) $record->status->value : (string) $record->status;
    }
}

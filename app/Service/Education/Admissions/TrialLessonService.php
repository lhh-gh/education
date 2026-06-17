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

namespace App\Service\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLead;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Model\Education\Admissions\EducationTrialLesson;
use App\Repository\Education\Admissions\LeadRepository;
use App\Repository\Education\Admissions\TrialLessonRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;

final class TrialLessonService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly TrialLessonRepository $repository
    ) {}

    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }

    public function create(array $data, EducationUserContext $context): array
    {
        $lead = $this->leadRepository->findScoped((int) $data['lead_id'], $context);
        if (! $lead instanceof EducationLead) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lead not found in current context', ['id' => (int) $data['lead_id']]);
        }
        $student = EducationLeadStudent::query()
            ->where('tenant_id', $context->tenantId)
            ->whereKey((int) $data['lead_student_id'])
            ->where('lead_id', (int) $lead->id)
            ->first();
        if (! $student instanceof EducationLeadStudent) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'lead student not found', ['lead_student_id' => (int) $data['lead_student_id']]);
        }

        $start = Carbon::parse((string) $data['start_time'])->toDateTimeString();
        $end = Carbon::parse((string) $data['end_time'])->toDateTimeString();
        if ($start >= $end) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'start_time must be before end_time', ['field' => 'start_time']);
        }
        $conflict = $this->repository->findConflict($context->tenantId, (int) $data['teacher_id'], isset($data['classroom_id']) ? (int) $data['classroom_id'] : null, $start, $end);
        if ($conflict instanceof EducationTrialLesson) {
            if ((int) $conflict->teacher_id === (int) $data['teacher_id']) {
                throw new BusinessException(ResultCode::CONFLICT, 'teacher time conflict', ['teacher_id' => (int) $data['teacher_id'], 'conflict_lesson_id' => (int) $conflict->id]);
            }
            throw new BusinessException(ResultCode::CONFLICT, 'classroom time conflict', ['classroom_id' => (int) $data['classroom_id'], 'conflict_lesson_id' => (int) $conflict->id]);
        }

        $lesson = $this->repository->create([
            'tenant_id' => $context->tenantId,
            'campus_id' => $lead->campus_id,
            'lead_id' => (int) $lead->id,
            'lead_student_id' => (int) $student->id,
            'course_id' => (int) $data['course_id'],
            'teacher_id' => (int) $data['teacher_id'],
            'classroom_id' => $data['classroom_id'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'scheduled',
            'consultant_user_id' => $data['consultant_user_id'] ?? $lead->owner_user_id,
            'remark' => $data['remark'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $lead->update(['stage' => 'trial_scheduled', 'updated_by' => $context->userId]);

        return $lesson->refresh()->toArray();
    }
}

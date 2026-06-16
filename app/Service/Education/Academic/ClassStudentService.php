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
use App\Model\Education\Academic\EducationStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Repository\Education\Academic\ClassRepository;
use App\Repository\Education\Academic\ClassStudentRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ClassStudentService
{
    public function __construct(
        private readonly ClassRepository $classRepository,
        private readonly ClassStudentRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function listStudents(int $classId, EducationUserContext $context): array
    {
        $class = $this->classRepository->findScoped($classId, $context);
        if (! $class instanceof EducationClass) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'class not found', ['id' => $classId]);
        }

        return $this->repository->listByClass($classId, $context);
    }

    public function saveStudents(int $classId, array $studentIds, EducationUserContext $context, ?int $operatorId): array
    {
        $class = $this->classRepository->findScoped($classId, $context);
        if (! $class instanceof EducationClass) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'class not found', ['id' => $classId]);
        }

        $studentIds = array_values(array_unique(array_map(static fn (mixed $row): int => \is_array($row) ? (int) $row['student_id'] : (int) $row, $studentIds)));
        if ((int) $class->max_students > 0 && \count($studentIds) > (int) $class->max_students) {
            throw new BusinessException(ResultCode::CONFLICT, 'class max students exceeded', ['max_students' => (int) $class->max_students]);
        }
        if ($class->class_type === 'one_to_one' && \count($studentIds) !== 1) {
            throw new BusinessException(ResultCode::CONFLICT, 'one-to-one class requires exactly one active student');
        }

        $rows = [];
        foreach ($studentIds as $studentId) {
            $student = $this->enabledStudent($studentId, (int) $class->tenant_id, (int) $class->campus_id);
            $account = $this->activeAccount($studentId, (int) $class->course_id, (int) $class->tenant_id, (int) $class->campus_id);
            $rows[] = [
                'tenant_id' => (int) $class->tenant_id,
                'campus_id' => (int) $class->campus_id,
                'class_id' => (int) $class->id,
                'course_id' => (int) $class->course_id,
                'student_id' => (int) $student->id,
                'account_id' => (int) $account->id,
                'student_name_snapshot' => $student->name,
                'student_no_snapshot' => $student->student_no,
                'status' => 'active',
            ];
        }

        $result = $this->repository->replaceStudents((int) $class->id, $rows, (int) $class->tenant_id, (int) $class->campus_id, $operatorId);
        $this->eventDispatcher->dispatch(new EducationAuditEvent(
            module: 'academic',
            resource: 'class_student',
            action: 'education.academic.class_student.saved',
            businessType: 'class',
            businessId: (int) $class->id,
            context: $context,
            beforeSnapshot: [],
            afterSnapshot: ['student_ids' => $studentIds],
            metadata: ['tenant_id' => (int) $class->tenant_id, 'campus_id' => (int) $class->campus_id],
            summary: 'Class students saved'
        ));

        return $result;
    }

    public function activeSnapshotsForScheduling(EducationClass $class): array
    {
        return $this->repository->activeStudentsByClass((int) $class->id, (int) $class->tenant_id, (int) $class->campus_id);
    }

    private function enabledStudent(int $studentId, int $tenantId, int $campusId): EducationStudent
    {
        $student = EducationStudent::query()->whereKey($studentId)->where('tenant_id', $tenantId)->where('campus_id', $campusId)->where('status', 'enabled')->first();
        if (! $student instanceof EducationStudent) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'student is disabled', ['student_id' => $studentId]);
        }

        return $student;
    }

    private function activeAccount(int $studentId, int $courseId, int $tenantId, int $campusId): EducationStudentCourseAccount
    {
        $account = EducationStudentCourseAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->first();
        if (! $account instanceof EducationStudentCourseAccount) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'active student course account is required', ['student_id' => $studentId, 'course_id' => $courseId]);
        }

        return $account;
    }
}

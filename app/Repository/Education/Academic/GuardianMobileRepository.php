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

use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Repository\IRepository;

/**
 * @extends IRepository<EducationStudentGuardian>
 */
final class GuardianMobileRepository extends IRepository
{
    public function __construct(
        protected readonly EducationStudentGuardian $model
    ) {}

    public function listBoundStudents(int $tenantId, int $guardianId): array
    {
        return $this->boundStudentQuery($tenantId, $guardianId)
            ->orderByDesc('edu_student_guardians.is_primary')
            ->orderBy('edu_students.name')
            ->get()
            ->toArray();
    }

    public function assertBoundStudent(int $tenantId, int $guardianId, int $studentId): array
    {
        $row = $this->boundStudentQuery($tenantId, $guardianId)
            ->where('edu_student_guardians.student_id', $studentId)
            ->first();

        return $row === null ? [] : (array) $row->toArray();
    }

    public function canSubmitLeave(int $tenantId, int $guardianId, int $studentId): bool
    {
        $binding = $this->assertBoundStudent($tenantId, $guardianId, $studentId);

        return $binding !== [] && (bool) $binding['can_submit_leave'];
    }

    public function pageStudentLessons(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
    {
        if ($this->assertBoundStudent($tenantId, $guardianId, $studentId) === []) {
            return ['list' => [], 'total' => 0];
        }

        $query = EducationLessonStudent::query()
            ->join('edu_lessons', 'edu_lessons.id', '=', 'edu_lesson_students.lesson_id')
            ->where('edu_lesson_students.tenant_id', $tenantId)
            ->where('edu_lesson_students.student_id', $studentId)
            ->select('edu_lesson_students.*', 'edu_lessons.title', 'edu_lessons.start_at', 'edu_lessons.end_at', 'edu_lessons.status as lesson_status', 'edu_lessons.class_name_snapshot', 'edu_lessons.course_name_snapshot', 'edu_lessons.teacher_name_snapshot', 'edu_lessons.classroom_name_snapshot');

        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->where('edu_lessons.start_at', '>=', (string) $params['start_at']);
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->where('edu_lessons.start_at', '<=', (string) $params['end_at']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('edu_lessons.status', (string) $params['status']);
        }

        $query->orderByDesc('edu_lessons.start_at');

        return $this->handlePage($query->paginate(perPage: $pageSize, pageName: self::PER_PAGE_PARAM_NAME, page: $page));
    }

    public function pageStudentAccounts(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
    {
        if ($this->assertBoundStudent($tenantId, $guardianId, $studentId) === []) {
            return ['list' => [], 'total' => 0];
        }

        $query = EducationStudentCourseAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId);

        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }

        $query->orderByDesc('id');

        return $this->handlePage($query->paginate(perPage: $pageSize, pageName: self::PER_PAGE_PARAM_NAME, page: $page));
    }

    public function pageStudentConsumptions(int $tenantId, int $guardianId, int $studentId, array $params, int $page, int $pageSize): array
    {
        if ($this->assertBoundStudent($tenantId, $guardianId, $studentId) === []) {
            return ['list' => [], 'total' => 0];
        }

        $query = EducationLessonConsumption::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId);

        if (isset($params['course_id']) && $params['course_id'] !== '') {
            $query->where('course_id', (int) $params['course_id']);
        }
        if (isset($params['account_id']) && $params['account_id'] !== '') {
            $query->where('account_id', (int) $params['account_id']);
        }
        if (isset($params['source_type']) && $params['source_type'] !== '') {
            $query->where('source_type', (string) $params['source_type']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }
        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->where('created_at', '>=', (string) $params['start_at']);
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->where('created_at', '<=', (string) $params['end_at']);
        }

        $query->orderByDesc('id');

        return $this->handlePage($query->paginate(perPage: $pageSize, pageName: self::PER_PAGE_PARAM_NAME, page: $page));
    }

    public function findLessonStudentForLeave(int $tenantId, int $guardianId, int $lessonStudentId): ?EducationLessonStudent
    {
        $lessonStudent = EducationLessonStudent::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($lessonStudentId)
            ->first();

        if (! $lessonStudent instanceof EducationLessonStudent) {
            return null;
        }

        return $this->canSubmitLeave($tenantId, $guardianId, (int) $lessonStudent->student_id)
            ? $lessonStudent
            : null;
    }

    private function boundStudentQuery(int $tenantId, int $guardianId)
    {
        return $this->getQuery()
            ->join('edu_students', 'edu_students.id', '=', 'edu_student_guardians.student_id')
            ->where('edu_student_guardians.tenant_id', $tenantId)
            ->where('edu_student_guardians.guardian_id', $guardianId)
            ->where('edu_students.tenant_id', $tenantId)
            ->where('edu_students.status', 'enabled')
            ->select([
                'edu_student_guardians.id as binding_id',
                'edu_student_guardians.student_id',
                'edu_student_guardians.guardian_id',
                'edu_student_guardians.relation',
                'edu_student_guardians.is_primary',
                'edu_student_guardians.can_receive_notice',
                'edu_student_guardians.can_submit_leave',
                'edu_students.name as student_name',
                'edu_students.student_no',
                'edu_students.campus_id',
            ]);
    }
}

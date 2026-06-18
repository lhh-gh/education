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
use App\Model\Education\Academic\EducationCourse;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Academic\CourseRepository;
use App\Repository\Education\Academic\TeacherCourseRepository;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TeacherCourseService
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly TeacherCourseRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function listTeachers(int $courseId, EducationUserContext $context): array
    {
        $course = $this->findCourseOrFail($courseId, $context);

        return $this->repository->listByCourse((int) $course->id, $context);
    }

    /**
     * @param int[] $teacherIds
     */
    public function saveTeachers(int $courseId, array $teacherIds, EducationUserContext $context, ?int $operatorId): array
    {
        $course = $this->findCourseOrFail($courseId, $context);
        $teacherIds = array_values(array_unique(array_map('intval', $teacherIds)));
        $this->assertTeachersCanBeAuthorized($teacherIds, $course, $context);

        return Db::transaction(function () use ($course, $teacherIds, $context, $operatorId): array {
            $before = $this->repository->listByCourse((int) $course->id, $context);
            $after = $this->repository->replaceEnabledTeachers(
                (int) $course->id,
                $teacherIds,
                (int) $course->tenant_id,
                (int) $course->campus_id,
                $operatorId
            );
            $this->eventDispatcher->dispatch(new EducationAuditEvent(
                module: 'academic',
                resource: 'course_teacher',
                action: 'education.academic.course_teacher.saved',
                businessType: 'course',
                businessId: (int) $course->id,
                context: $context,
                beforeSnapshot: ['teachers' => $before],
                afterSnapshot: ['teachers' => $after],
                metadata: ['tenant_id' => (int) $course->tenant_id, 'campus_id' => (int) $course->campus_id],
                summary: \sprintf('Course %s teacher authorizations saved', $course->name)
            ));

            return $after;
        });
    }

    public function assertTeacherCanTeachCourse(int $teacherId, int $courseId, int $tenantId, int $campusId): void
    {
        if (! $this->repository->teacherCanTeachCourse($teacherId, $courseId, $tenantId, $campusId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is not authorized for this course', ['teacher_id' => $teacherId, 'course_id' => $courseId]);
        }
    }

    private function findCourseOrFail(int $courseId, EducationUserContext $context): EducationCourse
    {
        $course = $this->courseRepository->findScoped($courseId, $context);
        if (! $course instanceof EducationCourse) {
            throw new BusinessException(ResultCode::NOT_FOUND, 'course not found in current context', ['id' => $courseId]);
        }

        if ($context->platformAccess || $context->roleCode === EducationRoleCode::TenantAdmin || $context->canAccessCampus((int) $course->campus_id)) {
            return $course;
        }

        throw new BusinessException(ResultCode::FORBIDDEN, 'course is outside current campus scope', ['id' => $courseId, 'campus_id' => (int) $course->campus_id]);
    }

    /**
     * @param int[] $teacherIds
     */
    private function assertTeachersCanBeAuthorized(array $teacherIds, EducationCourse $course, EducationUserContext $context): void
    {
        foreach ($teacherIds as $teacherId) {
            $teacher = EducationTeacher::query()->whereKey($teacherId)->first();
            if (! $teacher instanceof EducationTeacher) {
                throw new BusinessException(ResultCode::NOT_FOUND, 'teacher not found in current context', ['teacher_id' => $teacherId]);
            }
            if ((int) $teacher->tenant_id !== (int) $course->tenant_id || (int) $teacher->campus_id !== (int) $course->campus_id) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is outside current campus scope', ['teacher_id' => $teacherId]);
            }
            if ($teacher->status !== 'enabled') {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, 'teacher is disabled', ['teacher_id' => $teacherId]);
            }
            if (! $context->platformAccess && $context->roleCode !== EducationRoleCode::TenantAdmin && ! $context->canAccessCampus((int) $teacher->campus_id)) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is outside current campus scope', ['teacher_id' => $teacherId]);
            }
        }
    }
}

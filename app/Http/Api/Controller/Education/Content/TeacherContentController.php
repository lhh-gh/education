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

namespace App\Http\Api\Controller\Education\Content;

use App\Exception\BusinessException;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Content\TeacherLessonMaterialUsageRequest;
use App\Http\Api\Request\Education\Content\TeacherMaterialPageRequest;
use App\Http\Api\Request\Education\Content\TeacherStudentWorkSaveRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationTeacher;
use App\Model\Education\Content\EducationLearningMaterial;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Content\LessonMaterialUsageService;
use App\Service\Education\Content\StudentWorkService;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherContentController extends AbstractController
{
    public function __construct(
        private readonly MobileContextService $mobileContext,
        private readonly LessonMaterialUsageService $usageService,
        private readonly StudentWorkService $workService
    ) {}

    #[Get(path: '/mobile/education/content/teacher/materials', operationId: 'educationMobileContentTeacherMaterials', summary: 'Teacher content materials', tags: ['Education Mobile Content'])]
    #[PageResponse(instance: new Result())]
    public function materials(TeacherMaterialPageRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $teacherId = $this->teacherId($context);
        $tenantId = $this->tenantId($context);
        $courseId = isset($request->validated()['course_id']) ? (int) $request->validated()['course_id'] : null;
        if ($courseId !== null && ! $this->teacherCanUseCourse($tenantId, $teacherId, $courseId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is not authorized for this course', ['course_id' => $courseId]);
        }
        $query = EducationLearningMaterial::query()->where('tenant_id', $tenantId);
        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }
        $rows = $query->orderByDesc('id')->get()->toArray();

        return $this->success(['list' => $rows, 'total' => \count($rows)]);
    }

    #[Post(path: '/mobile/education/content/teacher/lesson-material-usages', operationId: 'educationMobileContentTeacherUsage', summary: 'Teacher content usage', tags: ['Education Mobile Content'])]
    #[ResultResponse(instance: new Result())]
    public function createUsage(TeacherLessonMaterialUsageRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $tenantId = $this->tenantId($context);
        $teacherId = $this->teacherId($context);
        $lessonIds = EducationLesson::query()->where('tenant_id', $tenantId)->where('teacher_id', $teacherId)->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        $data = $request->validated() + ['tenant_id' => $tenantId, 'campus_id' => $context->currentCampusId, 'teacher_id' => $teacherId];

        return $this->success($this->usageService->createForTeacher($data, $lessonIds));
    }

    #[Post(path: '/mobile/education/content/teacher/student-works', operationId: 'educationMobileContentTeacherStudentWork', summary: 'Teacher content student work', tags: ['Education Mobile Content'])]
    #[ResultResponse(instance: new Result())]
    public function saveStudentWork(TeacherStudentWorkSaveRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $tenantId = $this->tenantId($context);
        $teacherId = $this->teacherId($context);
        $studentIds = EducationLesson::query()
            ->join('edu_lesson_students', 'edu_lesson_students.lesson_id', '=', 'edu_lessons.id')
            ->where('edu_lessons.tenant_id', $tenantId)
            ->where('edu_lessons.teacher_id', $teacherId)
            ->pluck('edu_lesson_students.student_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        return $this->success($this->workService->saveForTeacher($request->validated() + [
            'tenant_id' => $tenantId,
            'campus_id' => $context->currentCampusId,
            'teacher_id' => $teacherId,
        ], $studentIds));
    }

    private function tenantId(EducationUserContext $context): int
    {
        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant education profile is required');
        }

        return (int) $context->tenantId;
    }

    private function teacherId(EducationUserContext $context): int
    {
        $profile = EducationUserProfile::query()->where('tenant_id', $this->tenantId($context))->where('user_id', $context->userId)->first();
        $teacher = $profile === null ? null : EducationTeacher::query()->where('user_profile_id', $profile->id)->first();
        if ($teacher === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher profile is required');
        }

        return (int) $teacher->id;
    }

    private function teacherCanUseCourse(int $tenantId, int $teacherId, int $courseId): bool
    {
        return EducationLesson::query()
            ->where('tenant_id', $tenantId)
            ->where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->exists();
    }
}

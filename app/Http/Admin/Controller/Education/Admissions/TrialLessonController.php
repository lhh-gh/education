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

namespace App\Http\Admin\Controller\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Admissions\LeadPageRequest;
use App\Http\Admin\Request\Education\Admissions\TrialAttendanceSaveRequest;
use App\Http\Admin\Request\Education\Admissions\TrialLessonSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationTrialAttendance;
use App\Service\Education\Admissions\TrialLessonService;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class TrialLessonController extends AbstractController
{
    public function __construct(private readonly TrialLessonService $service) {}

    #[Get(path: '/admin/education/admissions/trial-lessons/page', operationId: 'educationAdmissionTrialLessonPage', summary: 'Trial lesson page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Admissions'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:admissions:trial:page')]
    public function page(LeadPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/admissions/trial-lessons', operationId: 'educationAdmissionTrialLessonCreate', summary: 'Create trial lesson', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Admissions'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:admissions:trial:create')]
    public function create(TrialLessonSaveRequest $request): Result
    {
        return $this->success($this->service->create($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/admissions/trial-lessons/{id}/attendance', operationId: 'educationAdmissionTrialAttendance', summary: 'Save trial attendance', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Admissions'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:admissions:trial:attendance')]
    public function attendance(int $id, TrialAttendanceSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $attendance = EducationTrialAttendance::query()->updateOrCreate([
            'tenant_id' => $context->tenantId,
            'trial_lesson_id' => $id,
            'lead_student_id' => (int) $data['lead_student_id'],
        ], [
            'campus_id' => $context->currentCampusId,
            'attendance_status' => $data['attendance_status'],
            'checked_by' => $context->userId,
            'checked_at' => Carbon::now()->toDateTimeString(),
            'remark' => $data['remark'] ?? null,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return $this->success($attendance->toArray());
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Operations\GuardianMakeupPageRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Education\Operations\EducationMakeupRecord;
use App\Model\Education\Operations\EducationRenewalAlert;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianOperationController extends AbstractController
{
    public function __construct(private readonly MobileContextService $mobileContext) {}

    #[Get(path: '/mobile/education/operations/guardian/changed-lessons', operationId: 'educationMobileGuardianChangedLessons', summary: 'Guardian changed lessons', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function changedLessons(GuardianMakeupPageRequest $request): Result
    {
        $studentId = (int) $request->validated()['student_id'];
        $this->assertBoundStudent($studentId);
        $rows = EducationLessonChangeRequest::query()
            ->where('tenant_id', $this->mobileContext->mobile()->tenantId)
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();

        return $this->success(['list' => $rows]);
    }

    #[Get(path: '/mobile/education/operations/guardian/makeup-entitlements', operationId: 'educationMobileGuardianMakeupEntitlements', summary: 'Guardian makeup entitlements', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function makeupEntitlements(GuardianMakeupPageRequest $request): Result
    {
        $data = $request->validated();
        $studentId = (int) $data['student_id'];
        $this->assertBoundStudent($studentId);
        $query = EducationMakeupEntitlement::query()->where('tenant_id', $this->mobileContext->mobile()->tenantId)->where('student_id', $studentId);
        if (isset($data['status']) && $data['status'] !== '') {
            $query->where('status', $data['status']);
        }

        return $this->success(['list' => $query->orderByDesc('id')->get()->map(static fn ($row): array => $row->toArray())->all()]);
    }

    #[Get(path: '/mobile/education/operations/guardian/makeup-records', operationId: 'educationMobileGuardianMakeupRecords', summary: 'Guardian makeup records', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function makeupRecords(GuardianMakeupPageRequest $request): Result
    {
        $studentId = (int) $request->validated()['student_id'];
        $this->assertBoundStudent($studentId);

        return $this->success(['list' => EducationMakeupRecord::query()->where('tenant_id', $this->mobileContext->mobile()->tenantId)->where('student_id', $studentId)->orderByDesc('id')->get()->map(static fn ($row): array => $row->toArray())->all()]);
    }

    #[Get(path: '/mobile/education/operations/guardian/renewal-alerts', operationId: 'educationMobileGuardianRenewalAlerts', summary: 'Guardian renewal alerts', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Operations'])]
    #[ResultResponse(instance: new Result())]
    public function renewalAlerts(GuardianMakeupPageRequest $request): Result
    {
        $studentId = (int) $request->validated()['student_id'];
        $this->assertBoundStudent($studentId);

        return $this->success(['list' => EducationRenewalAlert::query()->where('tenant_id', $this->mobileContext->mobile()->tenantId)->where('student_id', $studentId)->where('status', 'open')->orderByDesc('id')->get()->map(static fn ($row): array => $row->toArray())->all()]);
    }

    private function assertBoundStudent(int $studentId): void
    {
        $context = $this->mobileContext->mobile();
        if ($context->roleCode !== EducationRoleCode::Guardian) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian mobile role required', ['role_code' => $context->roleCode->value]);
        }

        $guardianIds = EducationGuardian::query()
            ->where('tenant_id', $context->tenantId)
            ->where('status', 'enabled')
            ->whereIn('mobile', static function ($query) use ($context): void {
                $query->from('edu_user_profiles')
                    ->select('mobile')
                    ->where('tenant_id', $context->tenantId)
                    ->where('user_id', $context->userId)
                    ->where('role_code', EducationRoleCode::Guardian->value)
                    ->where('status', 'enabled')
                    ->whereNotNull('mobile');
            })
            ->pluck('id')
            ->all();
        $guardianIds[] = $context->userId;

        $bound = EducationStudentGuardian::query()
            ->where('tenant_id', $context->tenantId)
            ->whereIn('guardian_id', array_values(array_unique(array_map('intval', $guardianIds))))
            ->where('student_id', $studentId)
            ->exists();
        if (! $bound) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }
    }
}

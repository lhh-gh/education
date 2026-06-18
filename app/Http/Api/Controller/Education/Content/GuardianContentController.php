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
use App\Http\Api\Request\Education\Content\GuardianContentPageRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationGuardian;
use App\Model\Education\Academic\EducationStudentGuardian;
use App\Model\Education\Content\EducationLearningMaterial;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Service\Education\Content\MaterialReadService;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianContentController extends AbstractController
{
    public function __construct(private readonly MobileContextService $mobileContext, private readonly MaterialReadService $readService) {}

    #[Get(path: '/mobile/education/content/guardian/students/{studentId}/materials', operationId: 'educationMobileContentGuardianMaterials', summary: 'Guardian content materials', tags: ['Education Mobile Content'])]
    #[PageResponse(instance: new Result())]
    public function materials(int $studentId, GuardianContentPageRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $tenantId = $this->tenantId($context);
        $guardianId = $this->guardianId($context);
        $this->assertBoundStudent($tenantId, $guardianId, $studentId);
        $rows = EducationLearningMaterial::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->where('guardian_visible', true)
            ->orderByDesc('id')
            ->get();
        foreach ($rows as $row) {
            $this->readService->markMaterialRead([
                'tenant_id' => $tenantId,
                'campus_id' => $row->campus_id,
                'material_id' => $row->id,
                'material_version_id' => $row->current_version_id,
                'student_id' => $studentId,
                'guardian_user_id' => $context->userId,
            ]);
        }

        return $this->success(['list' => $rows->toArray(), 'total' => $rows->count()]);
    }

    #[Get(path: '/mobile/education/content/guardian/students/{studentId}/showcases', operationId: 'educationMobileContentGuardianShowcases', summary: 'Guardian content showcases', tags: ['Education Mobile Content'])]
    #[PageResponse(instance: new Result())]
    public function showcases(int $studentId): Result
    {
        $context = $this->mobileContext->mobile();
        $tenantId = $this->tenantId($context);
        $guardianId = $this->guardianId($context);
        $this->assertBoundStudent($tenantId, $guardianId, $studentId);

        return $this->success(['list' => [], 'total' => 0]);
    }

    private function tenantId(EducationUserContext $context): int
    {
        if ($context->tenantId === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'tenant education profile is required');
        }

        return (int) $context->tenantId;
    }

    private function guardianId(EducationUserContext $context): int
    {
        $profile = EducationUserProfile::query()->where('tenant_id', $this->tenantId($context))->where('user_id', $context->userId)->first();
        $guardian = $profile === null ? null : EducationGuardian::query()->where('mobile', $profile->mobile)->first();
        if ($guardian === null) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'guardian profile is not bound');
        }

        return (int) $guardian->id;
    }

    private function assertBoundStudent(int $tenantId, int $guardianId, int $studentId): void
    {
        $bound = EducationStudentGuardian::query()
            ->where('tenant_id', $tenantId)
            ->where('guardian_id', $guardianId)
            ->where('student_id', $studentId)
            ->exists();
        if (! $bound) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'student is not bound to current guardian', ['student_id' => $studentId]);
        }
    }
}

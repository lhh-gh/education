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

namespace App\Http\Api\Controller\Education\Admissions;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Admissions\GuardianConsultationRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Admissions\LeadService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianConsultationController extends AbstractController
{
    public function __construct(
        private readonly MobileContextService $mobileContext,
        private readonly LeadService $leadService
    ) {}

    #[Post(path: '/mobile/education/admissions/guardian/consultations', operationId: 'educationMobileGuardianAdmissionConsultation', summary: 'Guardian admission consultation', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Admissions'])]
    #[ResultResponse(instance: new Result())]
    public function create(GuardianConsultationRequest $request): Result
    {
        $context = $this->mobileContext->mobile();
        $data = $request->validated();

        return $this->success($this->leadService->create([
            'campus_id' => $context->currentCampusId,
            'contact_name' => $data['contact_name'],
            'contact_mobile' => $data['contact_mobile'],
            'lead_students' => [[
                'name' => $data['student_name'],
                'grade' => isset($data['student_age']) ? (string) $data['student_age'] : null,
                'school' => $data['interested_course'] ?? null,
            ]],
        ], $context));
    }
}

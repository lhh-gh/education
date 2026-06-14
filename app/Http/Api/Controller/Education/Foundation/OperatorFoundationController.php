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

namespace App\Http\Api\Controller\Education\Foundation;

use App\Http\Api\Request\Education\Foundation\MobileContextRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
final class OperatorFoundationController extends AbstractController
{
    public function __construct(
        private readonly MobileContextService $service
    ) {}

    #[Get(
        path: '/mobile/education/foundation/operator/context',
        operationId: 'educationMobileOperatorContext',
        summary: 'Operator mobile context',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Mobile Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    public function context(MobileContextRequest $request): Result
    {
        return $this->success(
            $this->service->operatorContext($this->service->mobile(), $request->validated())
        );
    }
}

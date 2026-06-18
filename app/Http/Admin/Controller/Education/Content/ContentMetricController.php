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

namespace App\Http\Admin\Controller\Education\Content;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Common\Result;
use App\Service\Education\Content\ContentMetricService;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
final class ContentMetricController extends AbstractController
{
    public function __construct(private readonly ContentMetricService $service) {}

    #[ResultResponse(instance: new Result())]
    public function placeholder(): Result
    {
        return $this->success(['service' => $this->service::class]);
    }
}

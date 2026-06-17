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

namespace App\Http\Api\Controller\Education\Finance;

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Http\Api\Request\Education\Finance\PaymentCallbackRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use App\Service\Education\Finance\PaymentCallbackService;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
final class PaymentCallbackController extends AbstractController
{
    public function __construct(
        private readonly PaymentCallbackService $service,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Post(path: '/api/education/finance/payment-callbacks/wechat', operationId: 'educationFinanceWechatCallback', summary: 'WeChat payment callback', tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    public function wechat(PaymentCallbackRequest $request): Result
    {
        $result = $this->service->handleWechatCallback($request->validated());
        $this->events->dispatch(new EducationAuditEvent(
            module: 'finance',
            resource: 'payment_callback',
            action: 'education.finance.payment.callback_processed',
            businessType: 'payment_record',
            businessId: $result['payment_record_id'],
            context: null,
            afterSnapshot: $result,
            summary: 'education.finance.payment.callback_processed',
            actorType: 'system'
        ));

        return $this->success($result, $result['message']);
    }
}

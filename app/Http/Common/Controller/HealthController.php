<?php

declare(strict_types=1);

namespace App\Http\Common\Controller;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

final class HealthController
{
    public function __construct(
        private readonly ResponseInterface $response,
        private readonly Redis $redis
    ) {
    }

    public function index(): PsrResponseInterface
    {
        $checks = [
            'app' => 'mineadmin-education-saas',
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $healthy = $checks['database'] === 'ok' && $checks['redis'] === 'ok';
        $result = new Result(
            $healthy ? ResultCode::SUCCESS : ResultCode::FAIL,
            $healthy ? 'success' : 'service unavailable',
            $checks
        );

        return $this->response
            ->json($result->toArray())
            ->withStatus($healthy ? 200 : 503);
    }

    private function checkDatabase(): string
    {
        try {
            Db::select('SELECT 1 AS ok');
            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }

    private function checkRedis(): string
    {
        try {
            $pong = $this->redis->ping();
            return in_array($pong, ['PONG', '+PONG', true], true) ? 'ok' : 'failed';
        } catch (Throwable) {
            return 'failed';
        }
    }
}

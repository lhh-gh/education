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

namespace App\Schema\Education\Academic;

use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationV1AcceptanceSummarySchema')]
final class V1AcceptanceSummarySchema implements \JsonSerializable
{
    public function __construct(private readonly array $payload) {}

    public function jsonSerialize(): mixed
    {
        return [
            'overall_status' => $this->payload['overall_status'] ?? 'fail',
            'checked_at' => $this->payload['checked_at'] ?? null,
            'gates' => array_map(static fn (array $row): array => [
                'key' => $row['key'] ?? null,
                'name' => $row['name'] ?? null,
                'status' => $row['status'] ?? 'fail',
                'message' => $row['message'] ?? null,
                'evidence' => $row['evidence'] ?? [],
            ], $this->payload['gates'] ?? []),
            'ledger' => [
                'account_count' => $this->payload['ledger']['account_count'] ?? 0,
                'mismatch_count' => $this->payload['ledger']['mismatch_count'] ?? 0,
                'mismatches' => $this->payload['ledger']['mismatches'] ?? [],
            ],
            'modules' => [
                'foundation' => $this->module('foundation'),
                'v1_01' => $this->module('v1_01'),
                'v1_02' => $this->module('v1_02'),
                'v1_03' => $this->module('v1_03'),
                'v1_04' => $this->module('v1_04'),
                'v1_05' => $this->module('v1_05'),
                'v1_06' => $this->module('v1_06'),
                'v1_07' => $this->module('v1_07'),
                'v1_08' => $this->module('v1_08'),
            ],
            'next_action' => $this->payload['next_action'] ?? null,
        ];
    }

    private function module(string $key): mixed
    {
        return $this->payload['modules'][$key] ?? null;
    }
}

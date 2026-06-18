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

namespace App\Contract\Education\Ai;

final readonly class AiProviderResult
{
    /**
     * @param null|array<string, mixed> $resultJson
     */
    public function __construct(
        public string $text,
        public ?array $resultJson = null,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $costCents = 0
    ) {}

    public function totalTokens(): int
    {
        return $this->promptTokens + $this->completionTokens;
    }
}

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

interface AiProviderInterface
{
    /**
     * @param list<array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     */
    public function generate(array $messages, array $options = []): AiProviderResult;
}

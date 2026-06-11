<?php

declare(strict_types=1);

namespace App\Contract\Education\Foundation;

interface TenantContextInterface
{
    public function id(): int;
}

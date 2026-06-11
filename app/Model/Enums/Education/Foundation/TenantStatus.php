<?php

declare(strict_types=1);

namespace App\Model\Enums\Education\Foundation;

enum TenantStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}

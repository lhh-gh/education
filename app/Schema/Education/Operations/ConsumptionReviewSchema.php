<?php

declare(strict_types=1);

namespace App\Schema\Education\Operations;

final class ConsumptionReviewSchema implements \JsonSerializable
{
    public function __construct(private readonly array $data) {}

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Context\ApplicationContext;
use HyperfTests\Feature\Admin\ControllerCase;
use Psr\SimpleCache\CacheInterface;

abstract class EducationAdminControllerCase extends ControllerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearCurrentUserCache();
        $this->cleanEducationData();
    }

    protected function tearDown(): void
    {
        $this->cleanEducationData();
        $this->clearCurrentUserCache();
        parent::tearDown();
    }

    protected function cleanEducationData(): void
    {
        EducationCampus::query()->forceDelete();
        EducationTenant::query()->forceDelete();
    }

    protected function authHeaders(array $headers = []): array
    {
        return array_merge(['Authorization' => 'Bearer ' . $this->token], $headers);
    }

    protected function grantPermissions(string ...$codes): void
    {
        foreach ($codes as $code) {
            $this->forAddPermission($code);
        }
    }

    protected function clearCurrentUserCache(): void
    {
        ApplicationContext::getContainer()
            ->get(CacheInterface::class)
            ->delete((string) $this->user->id);
    }
}

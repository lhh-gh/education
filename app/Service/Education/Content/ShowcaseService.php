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

namespace App\Service\Education\Content;

use App\Repository\Education\Content\ShowcaseRepository;

final class ShowcaseService
{
    public function __construct(private readonly ShowcaseRepository $showcases) {}

    /**
     * @param array<string, mixed> $data
     * @return array{showcase_id: int, status: string}
     */
    public function save(array $data): array
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        $showcase = $this->showcases->save($data + ['status' => 'draft']);
        if (\is_array($items)) {
            $this->showcases->replaceItems((int) $showcase->tenant_id, $showcase->campus_id === null ? null : (int) $showcase->campus_id, (int) $showcase->id, $items);
        }

        return ['showcase_id' => (int) $showcase->id, 'status' => $this->statusValue($showcase->status)];
    }

    /**
     * @return array{showcase_id: int, status: string}
     */
    public function publish(int $tenantId, int $showcaseId): array
    {
        $showcase = $this->showcases->findInTenant($tenantId, $showcaseId);
        $showcase->status = 'published';
        $showcase->published_at = date('Y-m-d H:i:s');
        $showcase->save();

        return ['showcase_id' => $showcaseId, 'status' => 'published'];
    }

    /**
     * @return array{showcase_id: int, status: string}
     */
    public function withdraw(int $tenantId, int $showcaseId): array
    {
        $showcase = $this->showcases->findInTenant($tenantId, $showcaseId);
        $showcase->status = 'withdrawn';
        $showcase->withdrawn_at = date('Y-m-d H:i:s');
        $showcase->save();

        return ['showcase_id' => $showcaseId, 'status' => 'withdrawn'];
    }

    /**
     * @param list<int> $boundStudentIds
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageGuardianShowcases(int $tenantId, int $studentId, array $boundStudentIds): array
    {
        if (! \in_array($studentId, $boundStudentIds, true)) {
            throw new \RuntimeException('student is not bound to current guardian', 403);
        }

        return $this->showcases->pageGuardian($tenantId, $studentId);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}

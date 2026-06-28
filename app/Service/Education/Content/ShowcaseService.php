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
use App\Service\Education\Foundation\EducationUserContext;

final class ShowcaseService
{
    public function __construct(private readonly ShowcaseRepository $showcases) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->showcases->page($filters, $context, $page, $pageSize);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{showcase_id: int, status: string}
     */
    public function save(array $data, ?EducationUserContext $context = null): array
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        $showcase = $this->showcases->save($data + ['status' => 'draft'], $context);
        if (\is_array($items)) {
            $this->showcases->replaceItems((int) $showcase->tenant_id, $showcase->campus_id === null ? null : (int) $showcase->campus_id, (int) $showcase->id, $items);
        }

        return ['showcase_id' => (int) $showcase->id, 'status' => $this->statusValue($showcase->status)];
    }

    /**
     * @return array{showcase_id: int, status: string}
     */
    public function publish(EducationUserContext $context, int $showcaseId): array
    {
        $showcase = $this->showcases->findInContext($context, $showcaseId);
        $showcase->status = 'published';
        $showcase->published_at = date('Y-m-d H:i:s');
        $showcase->save();

        return ['showcase_id' => $showcaseId, 'status' => 'published'];
    }

    /**
     * @return array{showcase_id: int, status: string}
     */
    public function withdraw(EducationUserContext $context, int $showcaseId): array
    {
        $showcase = $this->showcases->findInContext($context, $showcaseId);
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

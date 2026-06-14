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

namespace App\Repository\Education\Foundation;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationFeatureFlag;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;
use App\Model\Education\Foundation\EducationUserProfile;
use App\Repository\IRepository;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<EducationUserProfile>
 */
final class MobileContextRepository extends IRepository
{
    public function __construct(
        protected readonly EducationUserProfile $model
    ) {}

    public function findProfileForUser(int $userId, ?int $tenantId): ?EducationUserProfile
    {
        $profile = $this->profileQueryForUser($userId, $tenantId)
            ->where('status', 'enabled')
            ->first();

        return $profile instanceof EducationUserProfile ? $profile : null;
    }

    public function findAnyProfileForUser(int $userId, ?int $tenantId): ?EducationUserProfile
    {
        $profile = $this->profileQueryForUser($userId, $tenantId)->first();

        return $profile instanceof EducationUserProfile ? $profile : null;
    }

    /**
     * @return array<int, array{campus_id:int, campus_name:string}>
     */
    public function listCampusScopes(int $tenantId, int $userId): array
    {
        return EducationUserCampusScope::query()
            ->from('edu_user_campus_scopes as scopes')
            ->join('edu_campuses as campuses', 'campuses.id', '=', 'scopes.campus_id')
            ->where('scopes.tenant_id', $tenantId)
            ->where('scopes.user_id', $userId)
            ->where('campuses.tenant_id', $tenantId)
            ->where('campuses.status', 'enabled')
            ->whereNull('campuses.deleted_at')
            ->orderBy('scopes.campus_id')
            ->get(['scopes.campus_id', 'campuses.name as campus_name'])
            ->map(static fn (mixed $row): array => [
                'campus_id' => (int) $row->campus_id,
                'campus_name' => (string) $row->campus_name,
            ])
            ->all();
    }

    public function findTenant(int $tenantId): ?EducationTenant
    {
        $tenant = EducationTenant::query()
            ->whereKey($tenantId)
            ->where('status', 'enabled')
            ->first();

        return $tenant instanceof EducationTenant ? $tenant : null;
    }

    public function findCampus(int $tenantId, int $campusId): ?EducationCampus
    {
        $campus = EducationCampus::query()
            ->whereKey($campusId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'enabled')
            ->first();

        return $campus instanceof EducationCampus ? $campus : null;
    }

    /**
     * @return array<int, array{campus_id:int, campus_name:string}>
     */
    public function listEnabledCampuses(int $tenantId): array
    {
        return EducationCampus::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'enabled')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static fn (EducationCampus $campus): array => [
                'campus_id' => (int) $campus->id,
                'campus_name' => (string) $campus->name,
            ])
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    public function listEnabledFeatureFlags(?int $tenantId): array
    {
        $ownerKeys = ['system'];
        if ($tenantId !== null) {
            $ownerKeys[] = 'tenant:' . $tenantId;
        }

        $now = Carbon::now()->toDateTimeString();
        $flags = EducationFeatureFlag::query()
            ->whereIn('owner_key', $ownerKeys)
            ->where('status', 'enabled')
            ->where(static function (Builder $query) use ($now): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $now);
            })
            ->where(static function (Builder $query) use ($now): void {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $now);
            })
            ->orderBy('feature_code')
            ->orderByRaw("case when owner_type = 'system' then 0 else 1 end")
            ->get();

        $result = [];
        foreach ($flags as $flag) {
            if (! $flag instanceof EducationFeatureFlag) {
                continue;
            }
            $result[(string) $flag->feature_code] = (bool) $flag->enabled;
        }

        return $result;
    }

    private function profileQueryForUser(int $userId, ?int $tenantId): Builder
    {
        return $this->getQuery()
            ->where('user_id', $userId)
            ->when(
                $tenantId === null,
                static fn (Builder $query): Builder => $query->whereNull('tenant_id'),
                static fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)
            );
    }
}

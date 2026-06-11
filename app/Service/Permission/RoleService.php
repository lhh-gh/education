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

namespace App\Service\Permission;

use App\Model\Permission\Role;
use App\Repository\Permission\MenuRepository;
use App\Repository\Permission\RoleRepository;
use App\Service\IService;
use Hyperf\Collection\Collection;

/**
 * @extends IService<Role>
 */
final class RoleService extends IService
{
    public function __construct(
        protected readonly RoleRepository $repository,
        protected readonly MenuRepository $menuRepository
    ) {}

    public function getRolePermission(int $id): Collection
    {
        $role = $this->repository->findById($id);
        if (! $role instanceof Role) {
            return new Collection();
        }

        return $role->menus()->get();
    }

    public function batchGrantPermissionsForRole(int $id, array $permissionsCode): void
    {
        if (\count($permissionsCode) === 0) {
            $role = $this->repository->findById($id);
            if ($role instanceof Role) {
                $role->menus()->detach();
            }
            return;
        }

        $role = $this->repository->findById($id);
        if (! $role instanceof Role) {
            return;
        }

        $role->menus()->sync(
            $this->menuRepository
                ->list([
                    'code' => $permissionsCode,
                ])
                ->map(static fn ($item) => $item->id)
                ->toArray()
        );
    }
}

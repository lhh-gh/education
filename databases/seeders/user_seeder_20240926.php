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
use App\Model\Permission\Role;
use App\Model\Permission\User;
use Hyperf\Database\Seeders\Seeder;

class user_seeder_20240926 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entity = User::query()->firstOrNew(['username' => 'admin']);
        $entity->fill([
            'username' => 'admin',
            'user_type' => '100',
            'nickname' => '创始人',
            'email' => 'admin@adminmine.com',
            'phone' => '16858888988',
            'signed' => '广阔天地，大有所为',
            'created_by' => 0,
            'updated_by' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $entity->exists) {
            $entity->password = 123456;
        }
        $entity->save();

        $role = Role::query()->updateOrCreate([
            'code' => 'SuperAdmin',
        ], [
            'name' => '超级管理员',
            'code' => 'SuperAdmin',
            'status' => 1,
        ]);
        $entity->roles()->sync($role, false);
    }
}

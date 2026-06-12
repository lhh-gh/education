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
use App\Model\Enums\User\Status;
use App\Model\Permission\Role;
use Hyperf\Database\Seeders\Seeder;

class EducationFoundationRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->roles() as $code => $name) {
            Role::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'status' => Status::Normal,
                    'sort' => 0,
                    'remark' => 'Education foundation role',
                ]
            );
        }
    }

    private function roles(): array
    {
        return [
            'education_platform_operator' => '教育平台运营',
            'education_tenant_admin' => '教育机构管理员',
            'education_principal' => '校长',
            'education_academic_staff' => '教务',
            'education_front_desk' => '前台',
            'education_teacher' => '教师',
            'education_finance' => '财务',
            'education_guardian' => '家长',
        ];
    }
}

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
use App\Model\Education\Foundation\EducationDictItem;
use App\Model\Education\Foundation\EducationDictType;
use Hyperf\Database\Seeders\Seeder;

class EducationFoundationDictionarySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->dictionaries() as $code => $dictionary) {
            $type = EducationDictType::query()->updateOrCreate(
                ['owner_key' => 'system', 'code' => $code],
                [
                    'owner_type' => 'system',
                    'tenant_id' => null,
                    'name' => $dictionary['name'],
                    'description' => $dictionary['description'],
                    'status' => 'enabled',
                    'is_locked' => true,
                    'sort_order' => $dictionary['sort_order'],
                ]
            );

            foreach ($dictionary['items'] as $sortOrder => $item) {
                EducationDictItem::query()->updateOrCreate(
                    [
                        'dict_type_id' => $type->id,
                        'value' => $item['value'],
                    ],
                    [
                        'owner_key' => 'system',
                        'dict_code' => $code,
                        'label' => $item['label'],
                        'color' => $item['color'],
                        'extra' => null,
                        'sort_order' => $sortOrder * 10,
                        'status' => 'enabled',
                        'is_default' => $sortOrder === 1,
                    ]
                );
            }
        }
    }

    private function dictionaries(): array
    {
        return [
            'common_status' => [
                'name' => 'Common status',
                'description' => 'Shared enabled and disabled status values',
                'sort_order' => 10,
                'items' => [
                    1 => ['label' => 'Enabled', 'value' => 'enabled', 'color' => 'green'],
                    2 => ['label' => 'Disabled', 'value' => 'disabled', 'color' => 'gray'],
                ],
            ],
            'gender' => [
                'name' => 'Gender',
                'description' => 'Student and guardian gender values',
                'sort_order' => 20,
                'items' => [
                    1 => ['label' => 'Unknown', 'value' => 'unknown', 'color' => 'gray'],
                    2 => ['label' => 'Male', 'value' => 'male', 'color' => 'blue'],
                    3 => ['label' => 'Female', 'value' => 'female', 'color' => 'pink'],
                ],
            ],
            'attendance_status' => [
                'name' => 'Attendance status',
                'description' => 'Lesson attendance states',
                'sort_order' => 30,
                'items' => [
                    1 => ['label' => 'Pending', 'value' => 'pending', 'color' => 'gray'],
                    2 => ['label' => 'Present', 'value' => 'present', 'color' => 'green'],
                    3 => ['label' => 'Absent', 'value' => 'absent', 'color' => 'red'],
                    4 => ['label' => 'Leave', 'value' => 'leave', 'color' => 'orange'],
                ],
            ],
            'lesson_consumption_status' => [
                'name' => 'Lesson consumption status',
                'description' => 'Course package lesson consumption states',
                'sort_order' => 40,
                'items' => [
                    1 => ['label' => 'Pending', 'value' => 'pending', 'color' => 'gray'],
                    2 => ['label' => 'Consumed', 'value' => 'consumed', 'color' => 'green'],
                    3 => ['label' => 'Skipped', 'value' => 'skipped', 'color' => 'orange'],
                    4 => ['label' => 'Refunded', 'value' => 'refunded', 'color' => 'red'],
                ],
            ],
            'leave_request_status' => [
                'name' => 'Leave request status',
                'description' => 'Leave approval workflow states',
                'sort_order' => 50,
                'items' => [
                    1 => ['label' => 'Submitted', 'value' => 'submitted', 'color' => 'blue'],
                    2 => ['label' => 'Approved', 'value' => 'approved', 'color' => 'green'],
                    3 => ['label' => 'Rejected', 'value' => 'rejected', 'color' => 'red'],
                    4 => ['label' => 'Canceled', 'value' => 'canceled', 'color' => 'gray'],
                ],
            ],
            'payment_status' => [
                'name' => 'Payment status',
                'description' => 'Finance payment states',
                'sort_order' => 60,
                'items' => [
                    1 => ['label' => 'Pending', 'value' => 'pending', 'color' => 'gray'],
                    2 => ['label' => 'Paid', 'value' => 'paid', 'color' => 'green'],
                    3 => ['label' => 'Refunded', 'value' => 'refunded', 'color' => 'orange'],
                    4 => ['label' => 'Canceled', 'value' => 'canceled', 'color' => 'red'],
                ],
            ],
        ];
    }
}

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
use App\Model\Education\Foundation\EducationFeatureFlag;
use Hyperf\Database\Seeders\Seeder;

class EducationFoundationFeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->flags() as $code => $flag) {
            EducationFeatureFlag::query()->updateOrCreate(
                ['owner_key' => 'system', 'feature_code' => $code],
                [
                    'owner_type' => 'system',
                    'tenant_id' => null,
                    'feature_name' => $flag['name'],
                    'description' => $flag['description'],
                    'enabled' => $flag['enabled'],
                    'config' => [],
                    'effective_from' => null,
                    'effective_to' => null,
                    'status' => 'enabled',
                    'is_locked' => true,
                ]
            );
        }
    }

    private function flags(): array
    {
        return [
            'education.v1.core_academic' => ['name' => 'V1 Core Academic', 'description' => 'Core tenant, campus, course, schedule, attendance, and consumption capabilities', 'enabled' => true],
            'education.v2.academic_operations' => ['name' => 'V2 Academic Operations', 'description' => 'Advanced academic operation capabilities', 'enabled' => false],
            'education.v3.admissions_crm' => ['name' => 'V3 Admissions CRM', 'description' => 'Lead, audition, conversion, and admissions pipeline capabilities', 'enabled' => false],
            'education.v4.finance_payment' => ['name' => 'V4 Finance Payment', 'description' => 'Payment, refund, invoice, and finance capabilities', 'enabled' => false],
            'education.v5.teacher_payroll' => ['name' => 'V5 Teacher Payroll', 'description' => 'Teacher settlement and payroll capabilities', 'enabled' => false],
            'education.v6.group_management' => ['name' => 'V6 Group Management', 'description' => 'Class group and cohort management capabilities', 'enabled' => false],
            'education.v7.family_service' => ['name' => 'V7 Family Service', 'description' => 'Guardian service and home-school communication capabilities', 'enabled' => false],
            'education.v8.ai_assistant' => ['name' => 'V8 AI Assistant', 'description' => 'AI assistant capabilities', 'enabled' => false],
            'education.v9.workflow_alerts' => ['name' => 'V9 Workflow Alerts', 'description' => 'Workflow, notification, and alert capabilities', 'enabled' => false],
            'education.v10.growth_conversion' => ['name' => 'V10 Growth Conversion', 'description' => 'Growth and conversion analytics capabilities', 'enabled' => false],
            'education.v11.course_standards' => ['name' => 'V11 Course Standards', 'description' => 'Course standards and teaching quality capabilities', 'enabled' => false],
            'education.v12.learning_content' => ['name' => 'V12 Learning Content', 'description' => 'Learning content and resource capabilities', 'enabled' => false],
        ];
    }
}

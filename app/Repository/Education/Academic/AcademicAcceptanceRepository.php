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

namespace App\Repository\Education\Academic;

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

final class AcademicAcceptanceRepository
{
    private const MODULE_TABLES = [
        'foundation' => ['edu_tenants', 'edu_campuses', 'edu_user_profiles', 'edu_user_campus_scopes'],
        'v1_01' => ['edu_students', 'edu_guardians', 'edu_student_guardians', 'edu_teachers'],
        'v1_02' => ['edu_courses', 'edu_lesson_packages', 'edu_enrollments', 'edu_student_course_accounts'],
        'v1_03' => ['edu_classes', 'edu_class_students', 'edu_lessons', 'edu_lesson_students'],
        'v1_04' => ['edu_lesson_attendances', 'edu_lesson_consumptions', 'edu_account_adjustments'],
        'v1_05' => ['edu_leave_requests', 'edu_lesson_change_records'],
        'v1_06' => ['edu_lessons', 'edu_lesson_attendances', 'edu_leave_requests'],
        'v1_07' => ['edu_guardians', 'edu_student_guardians', 'edu_notices', 'edu_notice_receipts'],
        'v1_08' => ['edu_student_course_accounts', 'edu_lesson_consumptions'],
    ];

    public function moduleTableHealth(EducationUserContext $context): array
    {
        $health = [];
        foreach (self::MODULE_TABLES as $module => $tables) {
            $missing = array_values(array_filter($tables, static fn (string $table): bool => ! Schema::hasTable($table)));
            $health[$module] = [
                'status' => $missing === [] ? 'pass' : 'fail',
                'missing_tables' => $missing,
            ];
        }

        return $health;
    }

    public function ledgerConsistency(EducationUserContext $context, ?int $campusId): array
    {
        $query = Db::table('edu_student_course_accounts')
            ->whereNull('deleted_at');
        $this->applyTenantAndCampus($query, $context, $campusId);
        $accounts = $query->get();
        $mismatches = [];
        foreach ($accounts as $account) {
            $accountId = (int) $account->id;
            $decrease = $this->ledgerUnits($context, $accountId, 'decrease');
            $rollback = $this->ledgerUnits($context, $accountId, 'increase');
            $expectedConsumed = $this->decimal($decrease - $rollback);
            $expectedAvailable = $this->decimal(
                (float) $account->purchased_units
                + (float) $account->bonus_units
                + (float) $account->adjusted_units
                - (float) $account->refunded_units
                - (float) $account->frozen_units
                - (float) $expectedConsumed
            );
            $actualConsumed = $this->decimal($account->consumed_units);
            $actualAvailable = $this->decimal($account->available_units);
            if ($actualConsumed !== $expectedConsumed || $actualAvailable !== $expectedAvailable) {
                $mismatches[] = [
                    'account_id' => $accountId,
                    'student_id' => (int) $account->student_id,
                    'course_id' => (int) $account->course_id,
                    'actual_consumed_units' => $actualConsumed,
                    'expected_consumed_units' => $expectedConsumed,
                    'actual_available_units' => $actualAvailable,
                    'expected_available_units' => $expectedAvailable,
                ];
            }
        }

        return [
            'account_count' => $accounts->count(),
            'mismatch_count' => \count($mismatches),
            'mismatches' => $mismatches,
        ];
    }

    public function acceptanceFixtureSummary(EducationUserContext $context, ?int $campusId): array
    {
        return [
            'student_count' => $this->countTable('edu_students', $context, $campusId),
            'course_count' => $this->countTable('edu_courses', $context, $campusId),
            'lesson_count' => $this->countTable('edu_lessons', $context, $campusId),
            'attendance_count' => $this->countTable('edu_lesson_attendances', $context, $campusId),
            'consumption_count' => $this->countTable('edu_lesson_consumptions', $context, $campusId),
            'leave_count' => $this->countTable('edu_leave_requests', $context, $campusId),
            'notice_count' => $this->countTable('edu_notices', $context, $campusId),
        ];
    }

    public function mobileReadinessSummary(EducationUserContext $context, ?int $campusId): array
    {
        return [
            'teacher_lessons' => $this->countTable('edu_lessons', $context, $campusId),
            'guardian_bindings' => $this->countTable('edu_student_guardians', $context, null, false),
            'guardian_notices' => $this->countTable('edu_notice_receipts', $context, null, false),
        ];
    }

    public function pcReadinessSummary(EducationUserContext $context, ?int $campusId): array
    {
        return [
            'students' => $this->countTable('edu_students', $context, $campusId),
            'accounts' => $this->countTable('edu_student_course_accounts', $context, $campusId),
            'consumptions' => $this->countTable('edu_lesson_consumptions', $context, $campusId),
            'notices' => $this->countTable('edu_notices', $context, $campusId),
        ];
    }

    private function ledgerUnits(EducationUserContext $context, int $accountId, string $direction): float
    {
        $query = Db::table('edu_lesson_consumptions')
            ->whereNull('deleted_at')
            ->where('account_id', $accountId)
            ->where('direction', $direction);
        if ($direction === 'decrease') {
            $query->where('source_type', 'attendance');
        } else {
            $query->where('status', 'active');
        }
        if ($context->tenantId !== null) {
            $query->where('tenant_id', $context->tenantId);
        }

        return (float) $query->sum('units');
    }

    private function countTable(string $table, EducationUserContext $context, ?int $campusId, bool $campusScoped = true): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        $query = Db::table($table);
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        $this->applyTenantAndCampus($query, $context, $campusId, $campusScoped && Schema::hasColumn($table, 'campus_id'));

        return (int) $query->count();
    }

    private function applyTenantAndCampus(mixed $query, EducationUserContext $context, ?int $campusId, bool $campusScoped = true): void
    {
        if ($context->tenantId === null) {
            $query->whereRaw('1 = 0');

            return;
        }
        $query->where('tenant_id', $context->tenantId);
        if (! $campusScoped) {
            return;
        }
        if ($context->roleCode === EducationRoleCode::TenantAdmin) {
            if ($campusId !== null) {
                $query->where('campus_id', $campusId);
            }

            return;
        }
        if ($campusId !== null) {
            $context->canAccessCampus($campusId) ? $query->where('campus_id', $campusId) : $query->whereRaw('1 = 0');

            return;
        }
        $context->campusIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('campus_id', $context->campusIds);
    }

    private function decimal(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

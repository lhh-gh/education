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

namespace HyperfTests\Feature\Education\Group;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Group\EducationApprovalInstance;
use App\Model\Education\Group\EducationApprovalLog;
use App\Model\Education\Group\EducationApprovalNode;
use App\Model\Education\Group\EducationApprovalTask;
use App\Model\Education\Group\EducationApprovalTemplate;
use App\Model\Education\Group\EducationCampusOrgRelation;
use App\Model\Education\Group\EducationContract;
use App\Model\Education\Group\EducationContractAttachment;
use App\Model\Education\Group\EducationContractParty;
use App\Model\Education\Group\EducationContractRenewal;
use App\Model\Education\Group\EducationDataPermissionScope;
use App\Model\Education\Group\EducationFranchiseRecord;
use App\Model\Education\Group\EducationGroupOperationMetric;
use App\Model\Education\Group\EducationOrgUnit;
use App\Model\Education\Group\EducationRiskAuditEvent;
use App\Model\Education\Group\EducationUserDataPermission;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Feature\Education\Academic\ProfileRecordAdminCase;

abstract class GroupApiCase extends ProfileRecordAdminCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureGroupTables();
        $this->cleanGroupData();
    }

    protected function tearDown(): void
    {
        $this->cleanGroupData();
        parent::tearDown();
    }

    /**
     * @return array{tenant: EducationTenant, campus: EducationCampus}
     */
    protected function groupFixture(string $code = 'group_api'): array
    {
        $tenant = $this->tenant($code);
        $campus = $this->campus($tenant);

        return compact('tenant', 'campus');
    }

    private function ensureGroupTables(): void
    {
        if (Schema::hasTable('edu_org_units')) {
            return;
        }

        $migration = $this->groupMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanGroupData(): void
    {
        EducationRiskAuditEvent::query()->whereRaw('1 = 1')->delete();
        EducationFranchiseRecord::query()->forceDelete();
        EducationGroupOperationMetric::query()->whereRaw('1 = 1')->delete();
        EducationContractRenewal::query()->whereRaw('1 = 1')->delete();
        EducationContractAttachment::query()->whereRaw('1 = 1')->delete();
        EducationContractParty::query()->whereRaw('1 = 1')->delete();
        EducationContract::query()->forceDelete();
        EducationApprovalLog::query()->whereRaw('1 = 1')->delete();
        EducationApprovalTask::query()->whereRaw('1 = 1')->delete();
        EducationApprovalInstance::query()->whereRaw('1 = 1')->delete();
        EducationApprovalNode::query()->whereRaw('1 = 1')->delete();
        EducationApprovalTemplate::query()->forceDelete();
        EducationUserDataPermission::query()->forceDelete();
        EducationDataPermissionScope::query()->forceDelete();
        EducationCampusOrgRelation::query()->whereRaw('1 = 1')->delete();
        EducationOrgUnit::query()->forceDelete();
    }

    private function groupMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_060000_create_v6_group_tables.php';
    }
}

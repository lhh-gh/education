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

namespace HyperfTests\Unit\Education\Workflow;

use App\Model\Education\Workflow\EducationOperationAlert;
use App\Model\Education\Workflow\EducationOperationAlertLog;
use App\Model\Education\Workflow\EducationWorkflowEscalationPolicy;
use App\Model\Education\Workflow\EducationWorkflowExecutionLog;
use App\Model\Education\Workflow\EducationWorkflowMetricDaily;
use App\Model\Education\Workflow\EducationWorkflowRule;
use App\Model\Education\Workflow\EducationWorkflowRuleAction;
use App\Model\Education\Workflow\EducationWorkflowRuleCondition;
use App\Model\Education\Workflow\EducationWorkflowSlaPolicy;
use App\Model\Education\Workflow\EducationWorkflowTask;
use App\Model\Education\Workflow\EducationWorkflowTaskAssignee;
use App\Model\Education\Workflow\EducationWorkflowTaskAttachment;
use App\Model\Education\Workflow\EducationWorkflowTaskComment;
use App\Model\Education\Workflow\EducationWorkflowTaskLog;
use App\Model\Education\Workflow\EducationWorkflowTemplate;
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Schema;
use HyperfTests\Unit\Education\Family\FamilyTestCase;

abstract class WorkflowTestCase extends FamilyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureWorkflowTables();
        $this->cleanWorkflowData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function workflowFixture(string $code): array
    {
        return $this->familyFixture($code);
    }

    private function ensureWorkflowTables(): void
    {
        if (Schema::hasTable('edu_workflow_rules')) {
            return;
        }

        $migration = $this->workflowMigration();
        $migration->down();
        $migration->up();
    }

    private function cleanWorkflowData(): void
    {
        EducationWorkflowMetricDaily::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowExecutionLog::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTemplate::query()->forceDelete();
        EducationOperationAlertLog::query()->whereRaw('1 = 1')->delete();
        EducationOperationAlert::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowEscalationPolicy::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowSlaPolicy::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTaskAttachment::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTaskComment::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTaskLog::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTaskAssignee::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowTask::query()->forceDelete();
        EducationWorkflowRuleAction::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowRuleCondition::query()->whereRaw('1 = 1')->delete();
        EducationWorkflowRule::query()->forceDelete();
    }

    private function workflowMigration(): Migration
    {
        return require BASE_PATH . '/databases/migrations/2026_06_10_090000_create_v9_workflow_tables.php';
    }
}

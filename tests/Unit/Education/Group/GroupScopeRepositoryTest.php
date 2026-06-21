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

namespace HyperfTests\Unit\Education\Group;

use App\Model\Education\Group\EducationApprovalInstance;
use App\Model\Education\Group\EducationApprovalTask;
use App\Model\Education\Group\EducationApprovalTemplate;
use App\Model\Education\Group\EducationContract;
use App\Model\Education\Group\EducationContractRenewal;
use App\Model\Education\Group\EducationDataPermissionScope;
use App\Model\Education\Group\EducationFranchiseRecord;
use App\Model\Education\Group\EducationGroupOperationMetric;
use App\Model\Education\Group\EducationOrgUnit;
use App\Model\Education\Group\EducationRiskAuditEvent;
use App\Model\Education\Group\EducationUserDataPermission;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Group\ApprovalInstanceRepository;
use App\Repository\Education\Group\ApprovalTemplateRepository;
use App\Repository\Education\Group\ContractRenewalRepository;
use App\Repository\Education\Group\ContractRepository;
use App\Repository\Education\Group\DataPermissionRepository;
use App\Repository\Education\Group\FranchiseRepository;
use App\Repository\Education\Group\GroupMetricRepository;
use App\Repository\Education\Group\OrgUnitRepository;
use App\Repository\Education\Group\RiskAuditRepository;
use App\Service\Education\Foundation\EducationUserContext;

/**
 * @internal
 * @coversNothing
 */
final class GroupScopeRepositoryTest extends GroupTestCase
{
    public function testContractRepositoryUsesCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('group_scope_contract');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $repository = make(ContractRepository::class);

        $visible = $this->createContract((int) $tenant->id, (int) $visibleCampus->id, 'CT-SCOPE-001');
        $hidden = $this->createContract((int) $tenant->id, (int) $hiddenCampus->id, 'CT-SCOPE-002');

        $page = $repository->page([], $context, []);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertSame((int) $visible->id, (int) $repository->find((int) $visible->id, $context)?->id);
        self::assertNull($repository->find((int) $hidden->id, $context));
    }

    public function testGroupListRepositoriesUseCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('group_scope_lists');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);

        EducationContractRenewal::query()->create($this->contractRenewalData((int) $tenant->id, (int) $visibleCampus->id, 101));
        EducationContractRenewal::query()->create($this->contractRenewalData((int) $tenant->id, (int) $hiddenCampus->id, 102));
        EducationGroupOperationMetric::query()->create($this->metricData((int) $tenant->id, (int) $visibleCampus->id));
        EducationGroupOperationMetric::query()->create($this->metricData((int) $tenant->id, (int) $hiddenCampus->id));
        EducationFranchiseRecord::query()->create($this->franchiseData((int) $tenant->id, (int) $visibleCampus->id, 'FR-SCOPE-001'));
        EducationFranchiseRecord::query()->create($this->franchiseData((int) $tenant->id, (int) $hiddenCampus->id, 'FR-SCOPE-002'));

        self::assertSame(1, make(ContractRenewalRepository::class)->page([], $context)['total']);
        self::assertSame(1, make(GroupMetricRepository::class)->page([], $context, [])['total']);
        self::assertSame(1, make(FranchiseRepository::class)->page([], $context)['total']);
    }

    public function testRiskAuditRepositoryUsesCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('group_scope_risk');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id, userId: 9301);
        $repository = make(RiskAuditRepository::class);

        $visible = EducationRiskAuditEvent::query()->create($this->riskData((int) $tenant->id, (int) $visibleCampus->id, 'visible_risk'));
        $hidden = EducationRiskAuditEvent::query()->create($this->riskData((int) $tenant->id, (int) $hiddenCampus->id, 'hidden_risk'));

        $page = $repository->page([], $context, []);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertNull($repository->markHandled((int) $hidden->id, $context));
        self::assertFalse((bool) $hidden->refresh()->handled);
        self::assertSame((int) $visible->id, (int) $repository->markHandled((int) $visible->id, $context)?->id);
    }

    public function testApprovalInstanceRepositoryUsesCurrentCampusScopeForPlatformContext(): void
    {
        $tenant = $this->tenant('group_scope_approval');
        $visibleCampus = $this->campus($tenant, 'visible');
        $hiddenCampus = $this->campus($tenant, 'hidden');
        $context = $this->platformContext((int) $tenant->id, (int) $visibleCampus->id);
        $repository = make(ApprovalInstanceRepository::class);

        $visibleInstance = EducationApprovalInstance::query()->create($this->approvalInstanceData((int) $tenant->id, (int) $visibleCampus->id, 201));
        $hiddenInstance = EducationApprovalInstance::query()->create($this->approvalInstanceData((int) $tenant->id, (int) $hiddenCampus->id, 202));
        $visibleTask = EducationApprovalTask::query()->create($this->approvalTaskData((int) $tenant->id, (int) $visibleCampus->id, (int) $visibleInstance->id));
        $hiddenTask = EducationApprovalTask::query()->create($this->approvalTaskData((int) $tenant->id, (int) $hiddenCampus->id, (int) $hiddenInstance->id));

        $page = $repository->pageTasks([], $context);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visibleTask->id, (int) $page['list'][0]['id']);
        self::assertSame((int) $visibleTask->id, (int) $repository->lockTask((int) $visibleTask->id, $context)?->id);
        self::assertNull($repository->lockTask((int) $hiddenTask->id, $context));
        self::assertSame((int) $visibleInstance->id, (int) $repository->findInstance((int) $visibleInstance->id, $context)?->id);
        self::assertNull($repository->findInstance((int) $hiddenInstance->id, $context));
    }

    public function testConfigurationRepositoriesUseTenantFilterForPlatformContext(): void
    {
        $hiddenTenant = $this->tenant('group_scope_config_hidden');
        $visibleTenant = $this->tenant('group_scope_config_visible');
        $context = new EducationUserContext(
            userId: 9302,
            tenantId: null,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: null
        );

        EducationApprovalTemplate::query()->create($this->approvalTemplateData((int) $hiddenTenant->id, 'TPL-HIDDEN'));
        $visibleTemplate = EducationApprovalTemplate::query()->create($this->approvalTemplateData((int) $visibleTenant->id, 'TPL-VISIBLE'));
        $hiddenScope = EducationDataPermissionScope::query()->create($this->dataPermissionScopeData((int) $hiddenTenant->id, 'SCOPE-HIDDEN'));
        $visibleScope = EducationDataPermissionScope::query()->create($this->dataPermissionScopeData((int) $visibleTenant->id, 'SCOPE-VISIBLE'));
        EducationUserDataPermission::query()->create($this->userDataPermissionData((int) $hiddenTenant->id, (int) $hiddenScope->id, 9401));
        $visiblePermission = EducationUserDataPermission::query()->create($this->userDataPermissionData((int) $visibleTenant->id, (int) $visibleScope->id, 9402));
        EducationOrgUnit::query()->create($this->orgUnitData((int) $hiddenTenant->id, 'ORG-HIDDEN'));
        $visibleOrg = EducationOrgUnit::query()->create($this->orgUnitData((int) $visibleTenant->id, 'ORG-VISIBLE'));

        $templatePage = make(ApprovalTemplateRepository::class)->page(['tenant_id' => (int) $visibleTenant->id], $context);
        $permissionPage = make(DataPermissionRepository::class)->page(['tenant_id' => (int) $visibleTenant->id], $context);
        $orgTree = make(OrgUnitRepository::class)->tree($context, ['tenant_id' => (int) $visibleTenant->id]);

        self::assertSame(1, $templatePage['total']);
        self::assertSame((int) $visibleTemplate->id, (int) $templatePage['list'][0]['id']);
        self::assertSame(1, $permissionPage['total']);
        self::assertSame((int) $visiblePermission->id, (int) $permissionPage['list'][0]['id']);
        self::assertCount(1, $orgTree);
        self::assertSame((int) $visibleOrg->id, (int) $orgTree[0]['id']);
    }

    private function platformContext(int $tenantId, int $currentCampusId, int $userId = 9300): EducationUserContext
    {
        return new EducationUserContext(
            userId: $userId,
            tenantId: $tenantId,
            roleCode: EducationRoleCode::PlatformSuperAdmin,
            platformAccess: true,
            campusIds: [],
            currentCampusId: $currentCampusId
        );
    }

    private function createContract(int $tenantId, int $campusId, string $contractNo): EducationContract
    {
        return EducationContract::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'contract_no' => $contractNo,
            'contract_type' => 'lease',
            'title' => $contractNo,
            'counterparty_name' => 'Counterparty',
            'amount_cents' => 100000,
            'status' => 'active',
            'start_date' => '2026-06-01',
            'end_date' => '2027-06-01',
            'risk_level' => 'normal',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function contractRenewalData(int $tenantId, int $campusId, int $contractId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'contract_id' => $contractId,
            'renewal_type' => 'lease',
            'status' => 'pending',
            'due_date' => '2026-07-01',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricData(int $tenantId, int $campusId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => '2026-06-20',
            'campus_count' => 1,
            'student_count' => 10,
            'revenue_cents' => 100000,
            'consumed_credits' => '12.00',
            'renewal_alert_count' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function franchiseData(int $tenantId, int $campusId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'franchise_code' => $code,
            'franchise_name' => $code,
            'status' => 'potential',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function riskData(int $tenantId, int $campusId, string $eventType): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'event_type' => $eventType,
            'risk_level' => 'high',
            'business_type' => 'contract',
            'business_id' => 1,
            'summary' => $eventType,
            'payload_json' => [],
            'handled' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function approvalInstanceData(int $tenantId, int $campusId, int $businessId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'template_id' => 1,
            'business_type' => 'contract',
            'business_id' => $businessId,
            'status' => 'pending',
            'initiator_id' => 9300,
            'payload_json' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function approvalTaskData(int $tenantId, int $campusId, int $instanceId): array
    {
        return [
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'approval_instance_id' => $instanceId,
            'node_id' => 1,
            'assignee_user_id' => 9300,
            'status' => 'pending',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function approvalTemplateData(int $tenantId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'template_code' => $code,
            'template_name' => $code,
            'business_type' => 'contract',
            'status' => 'enabled',
            'version' => 1,
            'config_json' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataPermissionScopeData(int $tenantId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'scope_code' => $code,
            'scope_name' => $code,
            'scope_type' => 'campus_set',
            'scope_value_json' => ['campus_ids' => [1]],
            'status' => 'enabled',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userDataPermissionData(int $tenantId, int $scopeId, int $userId): array
    {
        return [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'scope_id' => $scopeId,
            'scope_type' => 'campus_set',
            'status' => 'enabled',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orgUnitData(int $tenantId, string $code): array
    {
        return [
            'tenant_id' => $tenantId,
            'code' => $code,
            'name' => $code,
            'unit_type' => 'group',
            'path' => $code,
            'level' => 1,
            'status' => 'enabled',
            'sort_order' => 0,
        ];
    }
}

<script setup lang="ts">
import type { AuditActorType, AuditLogDetail, AuditLogListItem, AuditLogPageParams } from '../../api/foundation/auditLog.ts'
import { getAuditLogDetail, pageAuditLogs } from '../../api/foundation/auditLog.ts'
import useUserStore from '@/store/modules/useUserStore.ts'
import { auditLogActionsByPermission, defaultAuditLogSearch, normalizeAuditLogSearch, resetAuditLogSearch } from './auditLogRules.ts'
import AuditPayloadDrawer from './components/AuditPayloadDrawer.vue'

defineOptions({ name: 'EducationFoundationAuditLogList' })

const message = useMessage()
const userStore = useUserStore()
const loading = ref(false)
const detailLoading = ref(false)
const drawerVisible = ref(false)
const detailError = ref('')
const errorText = ref('')
const rows = ref<AuditLogListItem[]>([])
const total = ref(0)
const detail = ref<AuditLogDetail | null>(null)
const dateRange = ref<[string, string] | []>([])
const search = reactive<AuditLogPageParams>(defaultAuditLogSearch())

const permissions = computed(() => userStore.getPermissions())
const actions = computed(() => auditLogActionsByPermission(permissions.value))

const moduleOptions = [{ label: 'foundation', value: 'foundation' }]
const resourceOptions = ['tenant', 'campus', 'user_profile', 'campus_scope', 'dict_type', 'dict_item', 'feature_flag']
const actorTypeOptions: AuditActorType[] = ['admin', 'teacher', 'guardian', 'system']

async function loadAuditLogs() {
  loading.value = true
  try {
    const response = await pageAuditLogs(normalizeAuditLogSearch(search, dateRange.value))
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Audit logs failed to load'
    message.error(errorText.value)
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadAuditLogs()
}

function handleReset() {
  const reset = resetAuditLogSearch()
  Object.assign(search, reset.search)
  dateRange.value = reset.dateRange
  loadAuditLogs()
}

async function openPayload(row: AuditLogListItem) {
  drawerVisible.value = true
  detailLoading.value = true
  detailError.value = ''
  detail.value = null
  try {
    const response = await getAuditLogDetail(row.id)
    detail.value = response.data
  }
  catch (error: any) {
    detailError.value = error?.message ?? 'Audit payload failed to load'
    message.error(detailError.value)
  }
  finally {
    detailLoading.value = false
  }
}

onMounted(loadAuditLogs)
</script>

<template>
  <div class="mine-layout pt-3 education-foundation-page audit-log-page">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Audit Logs</span>
        </div>
      </template>

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable placeholder="action / summary / request" style="width: 220px;" />
        </el-form-item>
        <el-form-item label="Module">
          <el-select v-model="search.module" clearable placeholder="Module" style="width: 140px;">
            <el-option v-for="option in moduleOptions" :key="option.value" :label="option.label" :value="option.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="Resource">
          <el-select v-model="search.resource" clearable placeholder="Resource" style="width: 160px;">
            <el-option v-for="resource in resourceOptions" :key="resource" :label="resource" :value="resource" />
          </el-select>
        </el-form-item>
        <el-form-item label="Action">
          <el-input v-model="search.action" clearable placeholder="Exact action" style="width: 260px;" />
        </el-form-item>
        <el-form-item label="Business">
          <el-input v-model="search.business_type" clearable placeholder="Type" style="width: 140px;" />
        </el-form-item>
        <el-form-item label="Business ID">
          <el-input v-model="search.business_id" clearable placeholder="ID" style="width: 140px;" />
        </el-form-item>
        <el-form-item label="Actor">
          <el-select v-model="search.actor_type" clearable placeholder="Actor" style="width: 130px;">
            <el-option v-for="actorType in actorTypeOptions" :key="actorType" :label="actorType" :value="actorType" />
          </el-select>
        </el-form-item>
        <el-form-item label="Actor ID">
          <el-input-number v-model="search.actor_user_id" :min="1" :controls="false" placeholder="User ID" />
        </el-form-item>
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" placeholder="Tenant ID" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" placeholder="Campus ID" />
        </el-form-item>
        <el-form-item label="Created">
          <el-date-picker
            v-model="dateRange"
            type="datetimerange"
            value-format="YYYY-MM-DD HH:mm:ss"
            range-separator="to"
            start-placeholder="Start"
            end-placeholder="End"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            Search
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="created_at" label="Created" width="170" />
        <el-table-column prop="module" label="Module" width="120" />
        <el-table-column prop="resource" label="Resource" width="140" />
        <el-table-column prop="action" label="Action" min-width="260" show-overflow-tooltip />
        <el-table-column prop="business_type" label="Business" width="140" />
        <el-table-column prop="business_id" label="Business ID" width="120" />
        <el-table-column prop="actor_type" label="Actor" width="110" />
        <el-table-column prop="actor_user_id" label="Actor ID" width="120" />
        <el-table-column prop="campus_id" label="Campus ID" width="120" />
        <el-table-column prop="ip_address" label="IP" width="140" />
        <el-table-column prop="summary" label="Summary" min-width="220" show-overflow-tooltip />
        <el-table-column label="Actions" fixed="right" width="100">
          <template #default="{ row }">
            <el-button v-if="actions.canDetail" link type="primary" @click="openPayload(row)">
              Payload
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No audit logs" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadAuditLogs"
      />
    </el-card>

    <AuditPayloadDrawer
      v-model="drawerVisible"
      :detail="detail"
      :loading="detailLoading"
      :error="detailError"
    />
  </div>
</template>

<style scoped lang="scss">
.education-foundation-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

<script setup lang="ts">
import type { AuditActorType, AuditLogDetail, AuditLogListItem, AuditLogPageParams } from '../../api/foundation/auditLog.ts'
import { getAuditLogDetail, pageAuditLogs } from '../../api/foundation/auditLog.ts'
import useUserStore from '@/store/modules/useUserStore.ts'
import { auditLogActionsByPermission, defaultAuditLogSearch, normalizeAuditLogSearch, resetAuditLogSearch } from './auditLogRules.ts'
import AuditPayloadDrawer from './components/AuditPayloadDrawer.vue'
import { useMessage } from '@/hooks/useMessage.ts'

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

const moduleOptions = [{ label: '基础模块', value: 'foundation' }]
const resourceOptions = [
  { label: '机构', value: 'tenant' },
  { label: '校区', value: 'campus' },
  { label: '教育用户档案', value: 'user_profile' },
  { label: '校区范围', value: 'campus_scope' },
  { label: '字典类型', value: 'dict_type' },
  { label: '字典项', value: 'dict_item' },
  { label: '功能开关', value: 'feature_flag' },
]
const actorTypeOptions: Array<{ label: string, value: AuditActorType }> = [
  { label: '管理员', value: 'admin' },
  { label: '教师', value: 'teacher' },
  { label: '家长', value: 'guardian' },
  { label: '系统', value: 'system' },
]

async function loadAuditLogs() {
  loading.value = true
  try {
    const response = await pageAuditLogs(normalizeAuditLogSearch(search, dateRange.value))
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '审计日志加载失败'
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
    detailError.value = error?.message ?? '审计详情加载失败'
    message.error(detailError.value)
  }
  finally {
    detailLoading.value = false
  }
}

onMounted(loadAuditLogs)
</script>

<template>
  <div class="mine-layout education-foundation-page audit-log-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>审计日志</span>
        </div>
      </template>

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable placeholder="动作/摘要/请求ID" style="width: 220px;" />
        </el-form-item>
        <el-form-item label="模块">
          <el-select v-model="search.module" clearable placeholder="模块" style="width: 140px;">
            <el-option v-for="option in moduleOptions" :key="option.value" :label="option.label" :value="option.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="资源">
          <el-select v-model="search.resource" clearable placeholder="资源" style="width: 160px;">
            <el-option v-for="resource in resourceOptions" :key="resource.value" :label="resource.label" :value="resource.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="动作">
          <el-input v-model="search.action" clearable placeholder="精确动作名" style="width: 260px;" />
        </el-form-item>
        <el-form-item label="业务类型">
          <el-input v-model="search.business_type" clearable placeholder="类型" style="width: 140px;" />
        </el-form-item>
        <el-form-item label="业务ID">
          <el-input v-model="search.business_id" clearable placeholder="ID" style="width: 140px;" />
        </el-form-item>
        <el-form-item label="操作人类型">
          <el-select v-model="search.actor_type" clearable placeholder="操作人类型" style="width: 130px;">
            <el-option v-for="actorType in actorTypeOptions" :key="actorType.value" :label="actorType.label" :value="actorType.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="操作人ID">
          <el-input-number v-model="search.actor_user_id" :min="1" :controls="false" placeholder="用户ID" />
        </el-form-item>
        <el-form-item label="机构ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" placeholder="机构ID" />
        </el-form-item>
        <el-form-item label="校区ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" placeholder="校区ID" />
        </el-form-item>
        <el-form-item label="创建时间">
          <el-date-picker
            v-model="dateRange"
            type="datetimerange"
            value-format="YYYY-MM-DD HH:mm:ss"
            range-separator="至"
            start-placeholder="开始"
            end-placeholder="结束"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="created_at" label="创建时间" width="170" />
        <el-table-column prop="module" label="模块" width="120" />
        <el-table-column prop="resource" label="资源" width="140" />
        <el-table-column prop="action" label="动作" min-width="260" show-overflow-tooltip />
        <el-table-column prop="business_type" label="业务类型" width="140" />
        <el-table-column prop="business_id" label="业务ID" width="120" />
        <el-table-column prop="actor_type" label="操作人类型" width="110" />
        <el-table-column prop="actor_user_id" label="操作人ID" width="120" />
        <el-table-column prop="campus_id" label="校区ID" width="120" />
        <el-table-column prop="ip_address" label="IP" width="140" />
        <el-table-column prop="summary" label="摘要" min-width="220" show-overflow-tooltip />
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="{ row }">
            <el-button v-if="actions.canDetail" link type="primary" @click="openPayload(row)">
              详情
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无审计日志" />
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

<script setup lang="ts">
import type { ConfigOwnerType } from '../../api/foundation/dictionary.ts'
import type { FeatureFlagPageParams, FeatureFlagRecord, FeatureFlagSavePayload } from '../../api/foundation/featureFlag.ts'
import {
  deleteFeatureFlag,
  pageFeatureFlags,
  resolveFeatureFlag,
  updateFeatureFlag,
  updateFeatureFlagStatus,
} from '../../api/foundation/featureFlag.ts'
import useUserStore from '@/store/modules/useUserStore.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { dictionaryOwnerTypeOptions, featureFlagActionsByPermission } from './actionRules.ts'
import FeatureFlagForm from './components/FeatureFlagForm.vue'

defineOptions({ name: 'EducationFoundationFeatureFlagList' })

const message = useMessage()
const userStore = useUserStore()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const currentFlag = ref<FeatureFlagRecord | null>(null)
const rows = ref<FeatureFlagRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive<FeatureFlagPageParams>({
  page: 1,
  page_size: 20,
  owner_type: undefined,
  tenant_id: undefined,
  keyword: '',
  enabled: undefined,
  status: undefined,
})

const permissions = computed(() => userStore.getPermissions())
const isPlatformContext = computed(() => {
  const roles = userStore.getRoles()
  return permissions.value.includes('*')
    || roles.includes('SuperAdmin')
    || roles.includes('education_platform_operator')
    || roles.includes('platform_operator')
    || roles.includes('platform_super_admin')
    || hasAuth('education:foundation:tenant:page')
})
const ownerOptions = computed(() => dictionaryOwnerTypeOptions(isPlatformContext.value))
const canCreate = computed(() => featureFlagActionsByPermission(permissions.value, false, false).canCreate)

watch(isPlatformContext, (platform) => {
  if (!platform && search.owner_type !== 'tenant') {
    search.owner_type = 'tenant'
  }
}, { immediate: true })

watch(() => search.owner_type, (ownerType) => {
  if (ownerType === 'system') {
    search.tenant_id = undefined
  }
})

function normalizeSearch(): FeatureFlagPageParams {
  return {
    ...search,
    tenant_id: search.owner_type === 'system' ? undefined : search.tenant_id,
  }
}

function ownerTypeLabel(ownerType: ConfigOwnerType): string {
  return ownerType === 'system' ? '系统' : '租户'
}

function statusLabel(status: string): string {
  return status === 'enabled' ? '启用' : '停用'
}

function enabledLabel(enabled: boolean): string {
  return enabled ? '开启' : '关闭'
}

function flagActions(row: FeatureFlagRecord) {
  return featureFlagActionsByPermission(permissions.value, row.enabled, row.is_locked, row.status)
}

function toSavePayload(row: FeatureFlagRecord, enabled = row.enabled): FeatureFlagSavePayload {
  return {
    owner_type: row.owner_type,
    tenant_id: row.tenant_id,
    feature_code: row.feature_code,
    feature_name: row.feature_name,
    description: row.description ?? '',
    enabled,
    config: row.config,
    effective_from: row.effective_from,
    effective_to: row.effective_to,
    status: row.status,
    is_locked: row.is_locked,
  }
}

async function loadFlags() {
  loading.value = true
  try {
    const response = await pageFeatureFlags(normalizeSearch())
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '功能开关列表加载失败'
    message.error(errorText.value)
  }
  finally {
    loading.value = false
  }
}

function openCreate() {
  dialogMode.value = 'create'
  currentFlag.value = null
  dialogVisible.value = true
}

function openEdit(row: FeatureFlagRecord) {
  dialogMode.value = 'edit'
  currentFlag.value = row
  dialogVisible.value = true
}

async function changeEnabled(row: FeatureFlagRecord) {
  await updateFeatureFlag(row.id, toSavePayload(row, !row.enabled))
  await loadFlags()
}

async function changeStatus(row: FeatureFlagRecord) {
  const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
  await updateFeatureFlagStatus(row.id, nextStatus)
  await loadFlags()
}

async function removeFlag(row: FeatureFlagRecord) {
  await message.confirm('确认删除该功能开关？')
  await deleteFeatureFlag(row.id)
  await loadFlags()
}

async function resolveFlag(row: FeatureFlagRecord) {
  const response = await resolveFeatureFlag(row.feature_code, row.tenant_id ?? search.tenant_id)
  message.success(`${row.feature_code}：${enabledLabel(response.data.enabled)}（${response.data.owner_key ?? '-'}）`)
}

function onFormSuccess() {
  dialogVisible.value = false
  loadFlags()
}

onMounted(loadFlags)
</script>

<template>
  <div class="mine-layout pt-3 education-foundation-page feature-flag-page">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>功能开关</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新建功能开关
          </el-button>
        </div>
      </template>

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="归属">
          <el-select v-model="search.owner_type" clearable :disabled="!isPlatformContext" placeholder="全部" style="width: 120px">
            <el-option v-for="option in ownerOptions" :key="option.value" :label="option.label" :value="option.value" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="isPlatformContext && search.owner_type === 'tenant'" label="租户 ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" placeholder="tenant_id" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable placeholder="功能编码/名称" />
        </el-form-item>
        <el-form-item label="开关值">
          <el-select v-model="search.enabled" clearable placeholder="全部" style="width: 120px">
            <el-option label="开启" :value="true" />
            <el-option label="关闭" :value="false" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable placeholder="全部" style="width: 120px">
            <el-option label="启用" value="enabled" />
            <el-option label="停用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadFlags">
            查询
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="owner_type" label="归属" width="90">
          <template #default="{ row }">
            {{ ownerTypeLabel(row.owner_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="tenant_id" label="租户 ID" width="100" />
        <el-table-column prop="feature_code" label="功能编码" min-width="220" />
        <el-table-column prop="feature_name" label="功能名称" min-width="160" />
        <el-table-column prop="enabled" label="开关值" width="100">
          <template #default="{ row }">
            <el-tag :type="row.enabled ? 'success' : 'info'">
              {{ enabledLabel(row.enabled) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="effective_from" label="生效开始" width="180" />
        <el-table-column prop="effective_to" label="生效结束" width="180" />
        <el-table-column prop="status" label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
              {{ statusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="is_locked" label="锁定" width="90">
          <template #default="{ row }">
            <el-tag :type="row.is_locked ? 'warning' : 'info'">
              {{ row.is_locked ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="300">
          <template #default="{ row }">
            <el-button v-if="flagActions(row).canResolve" link type="primary" @click="resolveFlag(row)">
              解析
            </el-button>
            <el-button v-if="flagActions(row).canEdit" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="flagActions(row).enabledAction" link type="primary" @click="changeEnabled(row)">
              {{ row.enabled ? '关闭' : '开启' }}
            </el-button>
            <el-button v-if="flagActions(row).statusAction" link type="primary" @click="changeStatus(row)">
              {{ row.status === 'enabled' ? '停用' : '启用' }}
            </el-button>
            <el-button v-if="flagActions(row).canDelete" link type="danger" @click="removeFlag(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无功能开关" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.page_size"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadFlags"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新建功能开关' : '编辑功能开关'" width="640px">
      <FeatureFlagForm
        :key="`${dialogMode}-${currentFlag?.id ?? 'new'}`"
        :mode="dialogMode"
        :data="currentFlag"
        :platform-context="isPlatformContext"
        @success="onFormSuccess"
      />
    </el-dialog>
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

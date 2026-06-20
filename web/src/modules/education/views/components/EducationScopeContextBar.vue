<script setup lang="ts">
import { pageCampuses } from '../../api/foundation/campus.ts'
import { pageTenants } from '../../api/foundation/tenant.ts'
import { getEducationScopeSnapshot, clearEducationScope } from '@/composables/education/useEducationScope.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import {
  buildCampusScopeOptions,
  buildTenantScopeOptions,
  resolveEducationScopePayload,
  type EducationScopeOption,
} from './educationScopeContextRules.ts'

defineOptions({ name: 'EducationScopeContextBar' })

const message = useMessage()
const tenantId = ref<number | undefined>(getEducationScopeSnapshot().tenant_id)
const campusId = ref<number | undefined>(getEducationScopeSnapshot().campus_id)
const tenantOptions = ref<EducationScopeOption[]>([])
const campusOptions = ref<EducationScopeOption[]>([])
const tenantLoading = ref(false)
const campusLoading = ref(false)

async function loadTenants() {
  tenantLoading.value = true
  try {
    const response = await pageTenants({ page: 1, page_size: 200 })
    tenantOptions.value = buildTenantScopeOptions(response.data.list)
  }
  catch (error: any) {
    message.error(error?.message ?? '机构列表加载失败')
  }
  finally {
    tenantLoading.value = false
  }
}

async function loadCampuses(nextTenantId?: number) {
  campusOptions.value = []
  if (!nextTenantId) {
    return
  }

  campusLoading.value = true
  try {
    const response = await pageCampuses({ page: 1, page_size: 200, tenant_id: nextTenantId })
    campusOptions.value = buildCampusScopeOptions(response.data.list)
    if (campusId.value && !campusOptions.value.some(item => item.value === campusId.value && !item.disabled)) {
      campusId.value = undefined
    }
  }
  catch (error: any) {
    message.error(error?.message ?? '校区列表加载失败')
  }
  finally {
    campusLoading.value = false
  }
}

async function handleTenantChange(value?: number) {
  campusId.value = undefined
  await loadCampuses(value)
}

function applyScope() {
  if (!tenantId.value) {
    message.warning('请先选择机构')
    return
  }

  resolveEducationScopePayload(tenantId.value, campusId.value)
  message.success('教育上下文已切换')
}

function clearScope() {
  tenantId.value = undefined
  campusId.value = undefined
  campusOptions.value = []
  clearEducationScope()
  message.success('教育上下文已清空')
}

onMounted(async () => {
  await loadTenants()
  await loadCampuses(tenantId.value)
})
</script>

<template>
  <div class="education-scope-context-bar">
    <div class="scope-title">
      <ma-svg-icon name="i-material-symbols-domain-rounded" :size="18" />
      <span>教育上下文</span>
    </div>

    <el-select
      v-model="tenantId"
      class="scope-select"
      clearable
      filterable
      :loading="tenantLoading"
      placeholder="选择机构"
      @change="handleTenantChange"
    >
      <el-option
        v-for="item in tenantOptions"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        :disabled="item.disabled"
      />
    </el-select>

    <el-select
      v-model="campusId"
      class="scope-select"
      clearable
      filterable
      :disabled="!tenantId"
      :loading="campusLoading"
      placeholder="选择校区"
    >
      <el-option
        v-for="item in campusOptions"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        :disabled="item.disabled"
      />
    </el-select>

    <el-button type="primary" @click="applyScope">
      应用
    </el-button>
    <el-button @click="clearScope">
      清空
    </el-button>
  </div>
</template>

<style scoped lang="scss">
.education-scope-context-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  min-height: 48px;
  padding: 8px 12px;
  margin: 12px 12px 0;
  background: var(--el-bg-color);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 6px;

  .scope-title {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 6px;
    min-width: 100px;
    font-weight: 600;
    color: var(--el-text-color-primary);
  }

  .scope-select {
    width: 220px;
  }
}

@media (max-width: 768px) {
  .education-scope-context-bar {
    flex-wrap: wrap;

    .scope-select {
      width: 100%;
    }
  }
}
</style>

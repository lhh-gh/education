<script setup lang="ts">
import type { V1AcceptanceParams, V1AcceptanceSummary } from '../../api/academic/report.ts'
import { getV1AcceptanceSummary } from '../../api/academic/report.ts'
import ReportStateBlock from './components/ReportStateBlock.vue'
import { acceptanceOverallType, reportTagType } from './reportRules.ts'

defineOptions({ name: 'EducationV1AcceptanceReport' })

const loading = ref(false)
const errorText = ref('')
const summary = ref<V1AcceptanceSummary | null>(null)
const search = reactive<V1AcceptanceParams>({ include_detail: true })
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !summary.value ? 'empty' : null)

async function loadSummary() {
  loading.value = true
  try {
    const response = await getV1AcceptanceSummary(search)
    summary.value = response.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'V1 acceptance loading failed'
  }
  finally {
    loading.value = false
  }
}

onMounted(loadSummary)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>V1 Acceptance</span>
          <el-button :loading="loading" @click="loadSummary">Refresh</el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search">
        <el-form-item label="Tenant ID"><el-input-number v-model="search.tenant_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Campus ID"><el-input-number v-model="search.campus_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item><el-checkbox v-model="search.include_detail">Include Detail</el-checkbox></el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadSummary" />
      <template v-else-if="summary">
        <el-alert class="mb-3" :type="acceptanceOverallType(summary.overall_status)" show-icon :closable="false" :title="`Overall: ${summary.overall_status}`" />
        <el-table :data="summary.gates" row-key="key">
          <el-table-column prop="key" label="Key" min-width="220" />
          <el-table-column prop="name" label="Name" min-width="180" />
          <el-table-column label="Status" width="110"><template #default="{ row }"><el-tag :type="reportTagType(row.status)">{{ row.status }}</el-tag></template></el-table-column>
          <el-table-column prop="message" label="Message" min-width="260" />
          <el-table-column label="Evidence" min-width="260"><template #default="{ row }"><code>{{ JSON.stringify(row.evidence) }}</code></template></el-table-column>
        </el-table>
        <el-table v-if="summary.ledger.mismatch_count > 0" class="mt-4" :data="summary.ledger.mismatches" row-key="account_id">
          <el-table-column prop="account_id" label="Account ID" width="120" />
          <el-table-column prop="actual_consumed_units" label="Actual Consumed" />
          <el-table-column prop="expected_consumed_units" label="Expected Consumed" />
          <el-table-column prop="actual_available_units" label="Actual Available" />
          <el-table-column prop="expected_available_units" label="Expected Available" />
        </el-table>
        <el-alert class="mt-4" type="info" show-icon :closable="false" :title="summary.next_action" />
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-weight: 600;
}
</style>

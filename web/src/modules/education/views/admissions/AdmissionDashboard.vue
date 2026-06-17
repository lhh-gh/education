<script setup lang="ts">
import type { AdmissionOverview } from '../../api/admissions/dashboard.ts'
import { getAdmissionOverview } from '../../api/admissions/dashboard.ts'

defineOptions({ name: 'EducationAdmissionDashboard' })

const loading = ref(false)
const overview = ref<AdmissionOverview | null>(null)
const search = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, start_date: '', end_date: '' })

async function loadOverview() {
  loading.value = true
  try {
    const response = await getAdmissionOverview(search)
    overview.value = response.data
  }
  finally {
    loading.value = false
  }
}

onMounted(loadOverview)
</script>

<template>
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>Admissions Dashboard</span><el-button @click="loadOverview">Refresh</el-button></div></template>
      <el-skeleton v-if="loading" :rows="4" animated />
      <el-row v-else :gutter="12">
        <el-col v-for="item in [
          ['New Leads', overview?.new_leads_count ?? 0],
          ['Follows', overview?.follow_count ?? 0],
          ['Trials', overview?.trial_count ?? 0],
          ['Converted', overview?.converted_count ?? 0],
        ]" :key="item[0]" :span="6">
          <el-statistic :title="String(item[0])" :value="Number(item[1])" />
        </el-col>
      </el-row>
      <el-empty v-if="!loading && !overview" description="No dashboard data" />
    </el-card>
  </div>
</template>

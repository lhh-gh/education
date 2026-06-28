<script setup lang="ts">
import type { AdmissionOverview } from '../../api/admissions/dashboard.ts'
import { getAdmissionOverview } from '../../api/admissions/dashboard.ts'

defineOptions({ name: 'EducationAdmissionDashboard' })

const loading = ref(false)
const overview = ref<AdmissionOverview | null>(null)
const search = reactive({ start_date: '', end_date: '' })

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
      <template #header><div class="page-header"><span>招生看板</span><el-button @click="loadOverview">刷新</el-button></div></template>
      <el-skeleton v-if="loading" :rows="4" animated />
      <el-row v-else :gutter="12">
        <el-col v-for="item in [
          ['新增线索', overview?.new_leads_count ?? 0],
          ['跟进次数', overview?.follow_count ?? 0],
          ['试听数', overview?.trial_count ?? 0],
          ['已转化', overview?.converted_count ?? 0],
        ]" :key="item[0]" :span="6">
          <el-statistic :title="String(item[0])" :value="Number(item[1])" />
        </el-col>
      </el-row>
      <el-empty v-if="!loading && !overview" description="暂无招生看板数据" />
    </el-card>
  </div>
</template>

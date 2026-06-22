<script setup lang="ts">
import type { ConsultantMetricRecord } from '../../api/growth/consultant-metric.ts'
import { getConsultantMetrics } from '../../api/growth/consultant-metric.ts'
import { consultantConversionRate, consultantMetricFilterPayload, growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthConsultantMetricDashboard' })

const filters = reactive({ consultant_user_id: undefined as number | undefined, dateRange: undefined as [string, string] | undefined })
const rows = ref<ConsultantMetricRecord[]>([])
const loading = ref(false)

async function loadRows() {
  loading.value = true
  try {
    const response = await getConsultantMetrics(consultantMetricFilterPayload(filters))
    rows.value = response.data.list
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>顾问指标</span>
          <el-button type="primary" :loading="loading" @click="loadRows">
            {{ growthText.refresh }}
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.consultant">
          <el-input-number v-model="filters.consultant_user_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item :label="growthText.fields.date">
          <el-date-picker v-model="filters.dateRange" type="daterange" value-format="YYYY-MM-DD" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="consultant_user_id">
        <el-table-column prop="consultant_user_id" :label="growthText.fields.consultant" width="120" />
        <el-table-column prop="assigned_leads_count" :label="growthText.fields.assigned" width="110" />
        <el-table-column prop="follow_count" :label="growthText.fields.follows" width="100" />
        <el-table-column prop="trial_count" :label="growthText.fields.trials" width="100" />
        <el-table-column prop="converted_count" :label="growthText.fields.converted" width="120" />
        <el-table-column prop="lost_count" :label="growthText.fields.lost" width="100" />
        <el-table-column label="转化率" width="130">
          <template #default="{ row }">
            {{ consultantConversionRate(row) }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无顾问指标" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-growth-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
}
</style>

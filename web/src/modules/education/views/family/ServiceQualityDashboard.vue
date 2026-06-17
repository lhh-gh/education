<script setup lang="ts">
import type { ServiceQualityMetric } from '../../api/family/quality.ts'
import { getServiceQualityDashboard } from '../../api/family/quality.ts'
import { qualityFilterParams } from './familyRules.ts'

defineOptions({ name: 'EducationFamilyServiceQualityDashboard' })

const loading = ref(false)
const rows = ref<ServiceQualityMetric[]>([])
const filters = reactive({
  campus_id: undefined as number | undefined,
  teacher_id: undefined as number | undefined,
  student_id: undefined as number | undefined,
  start_date: '',
  end_date: '',
})

const totals = computed(() => rows.value.reduce((carry, row) => ({
  comment_count: carry.comment_count + (row.comment_count ?? 0),
  homework_review_count: carry.homework_review_count + (row.homework_review_count ?? 0),
  report_count: carry.report_count + (row.report_count ?? 0),
}), { comment_count: 0, homework_review_count: 0, report_count: 0 }))

async function loadRows() {
  loading.value = true
  try {
    const response = await getServiceQualityDashboard(qualityFilterParams(filters))
    rows.value = response.data
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-family-page pt-3">
    <div class="metric-grid mb-3">
      <el-card shadow="never">
        <div class="metric-title">
          Comments
        </div>
        <div class="metric-value">
          {{ totals.comment_count }}
        </div>
      </el-card>
      <el-card shadow="never">
        <div class="metric-title">
          Homework Reviews
        </div>
        <div class="metric-value">
          {{ totals.homework_review_count }}
        </div>
      </el-card>
      <el-card shadow="never">
        <div class="metric-title">
          Reports
        </div>
        <div class="metric-value">
          {{ totals.report_count }}
        </div>
      </el-card>
    </div>
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Service Quality</span>
          <el-button :loading="loading" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="metric_date" label="Date" width="140" />
        <el-table-column prop="teacher_id" label="Teacher" width="120" />
        <el-table-column prop="student_id" label="Student" width="120" />
        <el-table-column prop="comment_count" label="Comments" width="120" />
        <el-table-column prop="homework_review_count" label="Reviews" width="120" />
        <el-table-column prop="report_count" label="Reports" width="120" />
        <template #empty>
          <el-empty description="No quality metrics" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped>
.metric-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.metric-title {
  color: var(--el-text-color-secondary);
  font-size: 13px;
}

.metric-value {
  margin-top: 8px;
  font-size: 24px;
  font-weight: 600;
}
</style>

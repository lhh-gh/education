<script setup lang="ts">
import type { ServiceQualityMetric } from '../../api/family/quality.ts'
import { getServiceQualityDashboard } from '../../api/family/quality.ts'
import { familyMetricCards, qualityFilterParams } from './familyRules.ts'

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
const cards = computed(() => familyMetricCards(totals.value))

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
      <el-card v-for="item in cards" :key="item.title" shadow="never">
        <div class="metric-title">
          {{ item.title }}
        </div>
        <div class="metric-value">
          {{ item.value }}
        </div>
      </el-card>
    </div>
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>服务质量</span>
          <el-button :loading="loading" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="metric_date" label="日期" width="140" />
        <el-table-column prop="teacher_id" label="教师" width="120" />
        <el-table-column prop="student_id" label="学员" width="120" />
        <el-table-column prop="comment_count" label="评语数" width="120" />
        <el-table-column prop="homework_review_count" label="点评数" width="120" />
        <el-table-column prop="report_count" label="报告数" width="120" />
        <template #empty>
          <el-empty description="暂无服务质量指标" />
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
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.metric-value {
  margin-top: 8px;
  font-size: 24px;
  font-weight: 600;
}
</style>

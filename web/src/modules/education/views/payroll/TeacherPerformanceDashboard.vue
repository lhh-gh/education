<script setup lang="ts">
import type { TeacherPerformanceRecord, TeacherPerformanceSummary } from '../../api/payroll/performance.ts'
import { getTeacherPerformanceSummary, pageTeacherPerformance } from '../../api/payroll/performance.ts'
import { buildPerformanceParams, centsToYuan } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollTeacherPerformanceDashboard' })

const loading = ref(false)
const rows = ref<TeacherPerformanceRecord[]>([])
const total = ref(0)
const summary = ref<TeacherPerformanceSummary>({ teacher_count: 0, lesson_count: 0, salary_amount_cents: 0 })
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, teacher_id: undefined as number | undefined, metric_month: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const params = buildPerformanceParams(search)
    const [summaryResponse, pageResponse] = await Promise.all([
      getTeacherPerformanceSummary({ ...search, ...params }),
      pageTeacherPerformance({ ...search, ...params }),
    ])
    summary.value = summaryResponse.data
    rows.value = pageResponse.data.list
    total.value = pageResponse.data.total
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Teacher Performance</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Month">
          <el-date-picker v-model="search.metric_month" type="month" value-format="YYYY-MM" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" />
        </el-form-item>
        <el-form-item label="Teacher ID">
          <el-input-number v-model="search.teacher_id" :min="1" />
        </el-form-item>
      </el-form>
      <el-row :gutter="12" class="mb-3">
        <el-col :span="6">
          <el-statistic title="Teachers" :value="summary.teacher_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Lessons" :value="summary.lesson_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Salary" :value="centsToYuan(summary.salary_amount_cents)" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Satisfaction" :value="summary.average_satisfaction_score || '-'" />
        </el-col>
      </el-row>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="teacher_name" label="Teacher" min-width="150" />
        <el-table-column prop="metric_month" label="Month" width="120" />
        <el-table-column prop="lesson_count" label="Lessons" width="110" />
        <el-table-column prop="workload_units" label="Workload" width="120" />
        <el-table-column prop="attendance_rate" label="Attendance" width="120" />
        <el-table-column label="Salary" width="140">
          <template #default="{ row }">
            {{ centsToYuan(row.salary_amount_cents) }}
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

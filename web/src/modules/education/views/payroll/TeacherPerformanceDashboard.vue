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
          <span>教师绩效</span>
          <el-button type="primary" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="月份">
          <el-date-picker v-model="search.metric_month" type="month" value-format="YYYY-MM" />
        </el-form-item>
        <el-form-item label="校区 ID">
          <el-input-number v-model="search.campus_id" :min="1" />
        </el-form-item>
        <el-form-item label="教师 ID">
          <el-input-number v-model="search.teacher_id" :min="1" />
        </el-form-item>
      </el-form>
      <el-row :gutter="12" class="mb-3">
        <el-col :span="6">
          <el-statistic title="教师数" :value="summary.teacher_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="课次数" :value="summary.lesson_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="薪酬" :value="centsToYuan(summary.salary_amount_cents)" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="满意度" :value="summary.average_satisfaction_score || '-'" />
        </el-col>
      </el-row>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="teacher_name" label="教师" min-width="150" />
        <el-table-column prop="metric_month" label="月份" width="120" />
        <el-table-column prop="lesson_count" label="课次数" width="110" />
        <el-table-column prop="workload_units" label="工作量" width="120" />
        <el-table-column prop="attendance_rate" label="出勤率" width="120" />
        <el-table-column label="薪酬" width="140">
          <template #default="{ row }">
            {{ centsToYuan(row.salary_amount_cents) }}
          </template>
        </el-table-column>
      </el-table>
      <el-empty v-if="!loading && rows.length === 0" description="暂无教师绩效" />
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

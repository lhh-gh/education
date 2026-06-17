<script setup lang="ts">
import type { TeacherWorkloadPageParams, TeacherWorkloadRecord, TeacherWorkloadSummary } from '../../api/operations/workload.ts'
import { getTeacherWorkloadSummary, pageTeacherWorkloadRecords } from '../../api/operations/workload.ts'
import { operationTagType } from './operationRules.ts'

defineOptions({ name: 'EducationOperationTeacherWorkloadReport' })

const loading = ref(false)
const rows = ref<TeacherWorkloadRecord[]>([])
const total = ref(0)
const summary = ref<TeacherWorkloadSummary | null>(null)
const errorText = ref('')
const search = reactive<TeacherWorkloadPageParams>({ page: 1, pageSize: 20, campus_id: undefined, teacher_id: undefined, workload_type: undefined, start_at: undefined, end_at: undefined })

async function loadRows() {
  loading.value = true
  try {
    const [pageResponse, summaryResponse] = await Promise.all([
      pageTeacherWorkloadRecords(search),
      getTeacherWorkloadSummary(search),
    ])
    rows.value = pageResponse.data.list
    total.value = pageResponse.data.total
    summary.value = summaryResponse.data
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Teacher workload loading failed'
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-operation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Teacher Workloads</span>
          <el-button @click="loadRows">
            Export Current Page
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Campus">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Teacher">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Type">
          <el-select v-model="search.workload_type" clearable style="width: 150px;">
            <el-option label="main" value="main" />
            <el-option label="substitute" value="substitute" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            Search
          </el-button>
        </el-form-item>
      </el-form>
      <el-row v-if="summary" :gutter="12" class="summary-row">
        <el-col :span="6">
          <el-statistic title="Credits" :value="Number(summary.total_credits)" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Rows" :value="summary.row_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Students" :value="summary.student_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="Present" :value="summary.present_count" />
        </el-col>
      </el-row>
      <el-table v-loading="loading" :data="rows" row-key="id" show-summary>
        <el-table-column prop="teacher_id" label="Teacher" width="110" />
        <el-table-column label="Workload" width="120">
          <template #default="{ row }">
            <el-tag :type="operationTagType(row.workload_type)">
              {{ row.workload_type }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="lesson_type" label="Lesson Type" width="130" />
        <el-table-column prop="credits" label="Credits" width="100" />
        <el-table-column prop="student_count" label="Students" width="100" />
        <el-table-column prop="present_count" label="Present" width="100" />
        <el-table-column prop="recorded_at" label="Recorded At" width="180" />
        <template #empty>
          <el-empty description="No workload records" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-operation-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-alert,
  .search-form,
  .summary-row { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

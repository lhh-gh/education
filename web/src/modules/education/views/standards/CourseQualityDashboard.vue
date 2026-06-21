<script setup lang="ts">
import { getCourseQualityMetrics } from '../../api/standards/quality.ts'
import { qualityFilterPayload } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsCourseQualityDashboard' })

interface QualityMetricRow {
  course_id?: number
  feedback_count?: number
  average_score?: number
  pass_rate?: number
}

const loading = ref(false)
const filters = reactive({ course_id: undefined as number | undefined, start_date: '', end_date: '' })
const rows = ref<QualityMetricRow[]>([])

async function load() {
  loading.value = true
  try {
    const response = await getCourseQualityMetrics(qualityFilterPayload(filters))
    rows.value = response.data.list ?? []
  }
  finally {
    loading.value = false
  }
}

function resetSearch() {
  filters.course_id = undefined
  filters.start_date = ''
  filters.end_date = ''
  load()
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>质量看板</span>
        </div>
      </template>

      <el-form :inline="true" :model="filters" class="search-form">
        <el-form-item label="课程 ID">
          <el-input-number v-model="filters.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="开始日期">
          <el-date-picker v-model="filters.start_date" value-format="YYYY-MM-DD" placeholder="请选择开始日期" />
        </el-form-item>
        <el-form-item label="结束日期">
          <el-date-picker v-model="filters.end_date" value-format="YYYY-MM-DD" placeholder="请选择结束日期" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="load">
            查询
          </el-button>
          <el-button @click="resetSearch">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="course_id">
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column prop="feedback_count" label="反馈数" width="120" />
        <el-table-column prop="average_score" label="平均分" width="120" />
        <el-table-column prop="pass_rate" label="达标率" width="120" />
        <template #empty>
          <el-empty description="暂无质量指标" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

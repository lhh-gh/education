<script setup lang="ts">
import type { MaterialUsageMetricRow, StudentWorkMetricRow } from '../../api/content/types.ts'
import { getMaterialUsageMetrics, getStudentWorkMetrics } from '../../api/content/metric.ts'
import { metricFilterPayload } from './contentRules.ts'

defineOptions({ name: 'EducationContentMaterialUsageDashboard' })

const loading = ref(false)
const materialRows = ref<MaterialUsageMetricRow[]>([])
const workRows = ref<StudentWorkMetricRow[]>([])
const filters = reactive<{ campus_id?: number, course_id?: number, material_id?: number, dateRange?: [string, string] }>({})

async function loadMetrics() {
  loading.value = true
  try {
    const payload = metricFilterPayload(filters)
    const [material, work] = await Promise.all([
      getMaterialUsageMetrics(payload),
      getStudentWorkMetrics(payload),
    ])
    materialRows.value = material.data.list
    workRows.value = work.data.list
  }
  finally {
    loading.value = false
  }
}

onMounted(loadMetrics)
</script>

<template>
  <div class="mine-layout education-content-dashboard pt-3">
    <el-card shadow="never">
      <template #header>
        <span>使用看板</span>
      </template>
      <el-form inline>
        <el-form-item label="校区">
          <el-input-number v-model="filters.campus_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="课程">
          <el-input-number v-model="filters.course_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="资料">
          <el-input-number v-model="filters.material_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-button type="primary" @click="loadMetrics">
          刷新
        </el-button>
      </el-form>
      <el-row :gutter="16">
        <el-col :span="12">
          <el-table v-loading="loading" :data="materialRows" row-key="metric_date">
            <el-table-column prop="metric_date" label="日期" width="120" />
            <el-table-column prop="teacher_use_count" label="教师使用" width="130" />
            <el-table-column prop="guardian_read_count" label="家长阅读" width="140" />
            <el-table-column prop="favorite_count" label="收藏数" width="110" />
            <template #empty>
              <el-empty description="暂无资料指标" />
            </template>
          </el-table>
        </el-col>
        <el-col :span="12">
          <el-table v-loading="loading" :data="workRows" row-key="metric_date">
            <el-table-column prop="metric_date" label="日期" width="120" />
            <el-table-column prop="created_count" label="创建数" width="100" />
            <el-table-column prop="published_count" label="发布数" width="110" />
            <el-table-column prop="guardian_read_count" label="阅读数" width="100" />
            <template #empty>
              <el-empty description="暂无作品指标" />
            </template>
          </el-table>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

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
        <span>Material Usage Dashboard</span>
      </template>
      <el-form inline>
        <el-form-item label="Campus">
          <el-input-number v-model="filters.campus_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Course">
          <el-input-number v-model="filters.course_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Material">
          <el-input-number v-model="filters.material_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-button type="primary" @click="loadMetrics">
          Refresh
        </el-button>
      </el-form>
      <el-row :gutter="16">
        <el-col :span="12">
          <el-table v-loading="loading" :data="materialRows" row-key="metric_date">
            <el-table-column prop="metric_date" label="Date" width="120" />
            <el-table-column prop="teacher_use_count" label="Teacher Uses" width="130" />
            <el-table-column prop="guardian_read_count" label="Guardian Reads" width="140" />
            <el-table-column prop="favorite_count" label="Favorites" width="110" />
            <template #empty>
              <el-empty description="No material metrics" />
            </template>
          </el-table>
        </el-col>
        <el-col :span="12">
          <el-table v-loading="loading" :data="workRows" row-key="metric_date">
            <el-table-column prop="metric_date" label="Date" width="120" />
            <el-table-column prop="created_count" label="Created" width="100" />
            <el-table-column prop="published_count" label="Published" width="110" />
            <el-table-column prop="guardian_read_count" label="Reads" width="100" />
            <template #empty>
              <el-empty description="No work metrics" />
            </template>
          </el-table>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { getCourseQualityMetrics } from '../../api/standards/quality.ts'
import { qualityFilterPayload } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsCourseQualityDashboard' })

const filters = reactive({ course_id: undefined as number | undefined, start_date: '', end_date: '' })
const rows = ref<any[]>([])

async function load() {
  const response = await getCourseQualityMetrics(qualityFilterPayload(filters))
  rows.value = response.data.list ?? []
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>质量看板</span></template><el-form inline><el-form-item label="课程 ID"><el-input-number v-model="filters.course_id" :min="1" /></el-form-item><el-date-picker v-model="filters.start_date" value-format="YYYY-MM-DD" placeholder="开始日期" /><el-date-picker v-model="filters.end_date" value-format="YYYY-MM-DD" placeholder="结束日期" /><el-button type="primary" @click="load">刷新</el-button></el-form><el-table :data="rows"><el-table-column prop="course_id" label="课程" /><el-table-column prop="feedback_count" label="反馈数" /><el-table-column prop="average_score" label="平均分" /></el-table></el-card></div>
</template>

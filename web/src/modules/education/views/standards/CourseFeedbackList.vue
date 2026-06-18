<script setup lang="ts">
import { pageCourseFeedbackRecords } from '../../api/standards/quality.ts'

defineOptions({ name: 'EducationStandardsCourseFeedbackList' })

const filters = reactive({ course_id: undefined as number | undefined })
const rows = ref<any[]>([])

async function load() {
  const response = await pageCourseFeedbackRecords(filters)
  rows.value = response.data.list ?? []
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Course Feedback</span></template><el-form inline><el-input-number v-model="filters.course_id" :min="1" /><el-button @click="load">Search</el-button></el-form><el-table :data="rows"><el-table-column prop="feedback_type" label="Type" /><el-table-column prop="score" label="Score" /></el-table></el-card></div>
</template>

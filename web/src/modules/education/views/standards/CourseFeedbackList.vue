<script setup lang="ts">
import { pageCourseFeedbackRecords, saveCourseFeedbackRecord } from '../../api/standards/quality.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsCourseFeedbackList' })

const filters = reactive({ course_id: undefined as number | undefined })
const form = reactive({ course_id: 0, feedback_type: '', score: undefined as number | undefined, content: '' })
const rows = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:feedback:save'))

async function load() {
  const response = await pageCourseFeedbackRecords(filters)
  rows.value = response.data.list ?? []
}

async function save() {
  const response = await saveCourseFeedbackRecord(form)
  rows.value.unshift({ ...form, id: response.data.feedback_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>课程反馈</span></template><el-form inline><el-form-item label="课程 ID"><el-input-number v-model="filters.course_id" :min="1" /></el-form-item><el-button @click="load">查询</el-button></el-form><el-form v-if="canSave" inline><el-form-item label="课程 ID"><el-input-number v-model="form.course_id" :min="1" /></el-form-item><el-form-item label="反馈类型"><el-input v-model="form.feedback_type" /></el-form-item><el-form-item label="评分"><el-input-number v-model="form.score" :min="0" /></el-form-item><el-form-item label="内容"><el-input v-model="form.content" /></el-form-item><el-button type="primary" @click="save">保存</el-button></el-form><el-table :data="rows"><el-table-column prop="feedback_type" label="反馈类型" /><el-table-column prop="score" label="评分" /></el-table></el-card></div>
</template>

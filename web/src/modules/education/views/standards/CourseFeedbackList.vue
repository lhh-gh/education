<script setup lang="ts">
import type { CourseFeedbackPayload } from '../../api/standards/quality.ts'
import { saveCourseFeedbackRecord } from '../../api/standards/quality.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsCourseFeedbackList' })

interface CourseFeedbackRow extends CourseFeedbackPayload {
  id: number
}

const form = reactive<CourseFeedbackPayload>({
  course_id: 0,
  standard_version_id: undefined,
  feedback_type: 'teacher',
  score: undefined,
  content: '',
  source_type: '',
  source_id: undefined,
})
const rows = ref<CourseFeedbackRow[]>([])
const canSave = computed(() => hasAuth('education:standards:feedback:save'))

const feedbackTypeOptions = [
  { label: '教师反馈', value: 'teacher' },
  { label: '学员反馈', value: 'student' },
  { label: '家长反馈', value: 'guardian' },
  { label: '督导反馈', value: 'supervisor' },
]

function feedbackTypeLabel(value: string) {
  return feedbackTypeOptions.find(item => item.value === value)?.label ?? value
}

async function save() {
  const response = await saveCourseFeedbackRecord(form)
  rows.value.unshift({ ...form, id: response.data.feedback_id })
  form.score = undefined
  form.content = ''
  form.source_type = ''
  form.source_id = undefined
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>课程反馈</span>
        </div>
      </template>

      <el-form v-if="canSave" :inline="true" :model="form" class="save-form">
        <el-form-item label="课程 ID">
          <el-input-number v-model="form.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="标准版本 ID">
          <el-input-number v-model="form.standard_version_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="反馈类型">
          <el-select v-model="form.feedback_type" class="filter-select">
            <el-option v-for="item in feedbackTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="评分">
          <el-input-number v-model="form.score" :min="0" :max="100" :controls="false" />
        </el-form-item>
        <el-form-item label="反馈内容">
          <el-input v-model="form.content" clearable placeholder="请输入反馈内容" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存课程反馈
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column prop="standard_version_id" label="标准版本 ID" width="140" />
        <el-table-column label="反馈类型" width="120">
          <template #default="{ row }">
            {{ feedbackTypeLabel(row.feedback_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="score" label="评分" width="100" />
        <el-table-column prop="content" label="反馈内容" min-width="240" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无课程反馈" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .filter-select {
    width: 140px;
  }

  .save-form {
    margin-bottom: 16px;
  }
}
</style>

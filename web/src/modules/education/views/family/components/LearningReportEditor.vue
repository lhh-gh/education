<script setup lang="ts">
import { saveLearningReport } from '../../../api/family/report.ts'

const emit = defineEmits<{ success: [] }>()
const visible = defineModel<boolean>({ default: false })
const saving = ref(false)
const form = reactive({
  student_id: undefined as number | undefined,
  report_title: '',
  report_period: '',
  summary: '',
  item_title: '',
  item_content: '',
})

async function submit() {
  if (!form.student_id) {
    return
  }
  saving.value = true
  try {
    await saveLearningReport({
      student_id: form.student_id,
      report_title: form.report_title,
      report_period: form.report_period,
      summary: form.summary,
      items: form.item_title || form.item_content
        ? [{ item_type: 'summary', title: form.item_title, content: form.item_content }]
        : [],
    })
    visible.value = false
    emit('success')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <el-dialog v-model="visible" title="学习报告" width="640px">
    <el-form label-width="110px">
      <el-form-item label="学员ID">
        <el-input-number v-model="form.student_id" :min="1" />
      </el-form-item>
      <el-form-item label="标题">
        <el-input v-model="form.report_title" />
      </el-form-item>
      <el-form-item label="周期">
        <el-input v-model="form.report_period" placeholder="2026-06" />
      </el-form-item>
      <el-form-item label="摘要">
        <el-input v-model="form.summary" type="textarea" :rows="3" />
      </el-form-item>
      <el-form-item label="条目标题">
        <el-input v-model="form.item_title" />
      </el-form-item>
      <el-form-item label="条目内容">
        <el-input v-model="form.item_content" type="textarea" :rows="3" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        取消
      </el-button>
      <el-button type="primary" :loading="saving" @click="submit">
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { saveHomeworkAssignment } from '../../../api/family/homework.ts'

const emit = defineEmits<{ success: [] }>()
const visible = defineModel<boolean>({ default: false })
const saving = ref(false)
const form = reactive({
  title: '',
  content: '',
  due_at: '',
  student_ids_text: '',
})

async function submit() {
  saving.value = true
  try {
    const studentIds = form.student_ids_text.split(',').map(item => Number(item.trim())).filter(item => item > 0)
    await saveHomeworkAssignment({
      title: form.title,
      content: form.content,
      due_at: form.due_at || undefined,
      student_ids: studentIds,
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
  <el-dialog v-model="visible" title="课后作业" width="560px">
    <el-form label-width="110px">
      <el-form-item label="标题">
        <el-input v-model="form.title" />
      </el-form-item>
      <el-form-item label="内容">
        <el-input v-model="form.content" type="textarea" :rows="4" />
      </el-form-item>
      <el-form-item label="截止时间">
        <el-input v-model="form.due_at" placeholder="2026-06-15 20:00:00" />
      </el-form-item>
      <el-form-item label="学员ID">
        <el-input v-model="form.student_ids_text" placeholder="1201,1202" />
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

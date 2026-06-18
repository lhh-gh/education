<script setup lang="ts">
import type { NoticeRecord, NoticeSavePayload } from '../../../api/academic/notice.ts'
import { createNotice, updateNotice } from '../../../api/academic/notice.ts'
import { defaultNoticeForm, normalizeNoticeFormTarget, targetRequiresId } from '../noticeRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationNoticeForm' })

const props = defineProps<{
  modelValue: boolean
  row?: NoticeRecord | null
  tenantId?: number
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void
  (event: 'saved', row: NoticeRecord): void
}>()

const message = useMessage()
const submitting = ref(false)
const errorText = ref('')
const fieldError = ref('')
const form = reactive<NoticeSavePayload>(defaultNoticeForm(props.tenantId))
const isEdit = computed(() => !!props.row?.id)

watch(() => props.modelValue, (visible) => {
  if (!visible) {
    return
  }
  Object.assign(form, props.row
    ? {
        tenant_id: props.tenantId,
        campus_id: props.row.campus_id ?? undefined,
        notice_type: props.row.notice_type,
        target_type: props.row.target_type,
        target_id: props.row.target_id ?? undefined,
        title: props.row.title,
        content: props.row.content,
        priority: props.row.priority,
        expire_at: props.row.expire_at ?? undefined,
        remark: props.row.remark ?? '',
      }
    : defaultNoticeForm(props.tenantId))
  errorText.value = ''
  fieldError.value = ''
})

watch(() => form.target_type, () => {
  Object.assign(form, normalizeNoticeFormTarget(form))
})

async function handleSubmit() {
  submitting.value = true
  try {
    const payload = normalizeNoticeFormTarget({ ...form })
    const response = isEdit.value && props.row
      ? await updateNotice(props.row.id, payload)
      : await createNotice(payload)
    emit('saved', response.data)
    emit('update:modelValue', false)
    errorText.value = ''
    fieldError.value = ''
  }
  catch (error: any) {
    fieldError.value = error?.data?.field ?? ''
    errorText.value = error?.message ?? 'Notice save failed'
    message.error(errorText.value)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <el-drawer :model-value="modelValue" :title="isEdit ? 'Edit Notice' : 'Create Notice'" size="640px" @update:model-value="emit('update:modelValue', $event)">
    <el-alert v-if="errorText" class="mb-3" type="error" show-icon :closable="false" :title="errorText" />
    <el-form label-width="130px" :model="form">
      <el-form-item label="Campus ID" :error="fieldError === 'campus_id' ? errorText : ''">
        <el-input-number v-model="form.campus_id" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Type" required>
        <el-select v-model="form.notice_type">
          <el-option label="Academic" value="academic" />
          <el-option label="Activity" value="activity" />
          <el-option label="Fee" value="fee" />
          <el-option label="System" value="system" />
        </el-select>
      </el-form-item>
      <el-form-item label="Target" required>
        <el-segmented v-model="form.target_type" :options="[{ label: 'All', value: 'all' }, { label: 'Campus', value: 'campus' }, { label: 'Class', value: 'class' }, { label: 'Student', value: 'student' }]" />
      </el-form-item>
      <el-form-item label="Target ID" :required="targetRequiresId(form.target_type)" :error="fieldError === 'target_id' ? errorText : ''">
        <el-input-number v-model="form.target_id" :disabled="!targetRequiresId(form.target_type)" :min="1" :controls="false" />
      </el-form-item>
      <el-form-item label="Title" required :error="fieldError === 'title' ? errorText : ''">
        <el-input v-model="form.title" maxlength="160" show-word-limit />
      </el-form-item>
      <el-form-item label="Content" required :error="fieldError === 'content' ? errorText : ''">
        <el-input v-model="form.content" type="textarea" :rows="8" maxlength="5000" show-word-limit />
      </el-form-item>
      <el-form-item label="Priority" required>
        <el-select v-model="form.priority">
          <el-option label="Normal" value="normal" />
          <el-option label="Important" value="important" />
          <el-option label="Urgent" value="urgent" />
        </el-select>
      </el-form-item>
      <el-form-item label="Expire At">
        <el-date-picker v-model="form.expire_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" />
      </el-form-item>
      <el-form-item label="Remark">
        <el-input v-model="form.remark" type="textarea" :rows="3" maxlength="500" show-word-limit />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="emit('update:modelValue', false)">
        Cancel
      </el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        Save
      </el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts">
import { saveUserDataPermission } from '../../../api/group/permission.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const saving = ref(false)
const campusText = ref('')
const form = reactive({ user_id: undefined as number | undefined, scope_type: 'campus_set' as const, campus_ids: [] as number[] })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  saving.value = true
  try {
    form.campus_ids = campusText.value.split(',').map(item => Number(item.trim())).filter(Boolean)
    await saveUserDataPermission({ user_id: Number(form.user_id), scope_type: form.scope_type, campus_ids: form.campus_ids })
    visible.value = false
    emit('success')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <el-dialog v-model="visible" title="Data Permission" width="560px">
    <el-form :model="form" label-width="112px">
      <el-form-item label="User ID">
        <el-input-number v-model="form.user_id" :min="1" />
      </el-form-item>
      <el-form-item label="Scope">
        <el-segmented v-model="form.scope_type" :options="['group_all', 'org_tree', 'campus_set', 'self']" />
      </el-form-item>
      <el-form-item label="Campus IDs">
        <el-input v-model="campusText" placeholder="2001, 2002" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="saving" @click="submit">
        Save
      </el-button>
    </template>
  </el-dialog>
</template>

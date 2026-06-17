<script setup lang="ts">
import { saveOrgUnit } from '../../../api/group/org.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const saving = ref(false)
const form = reactive({ code: '', name: '', unit_type: 'group', status: 'enabled' })
const visible = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

async function submit() {
  saving.value = true
  try {
    await saveOrgUnit(form)
    visible.value = false
    emit('success')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <el-dialog v-model="visible" title="Org Unit" width="520px">
    <el-form :model="form" label-width="96px">
      <el-form-item label="Code">
        <el-input v-model="form.code" />
      </el-form-item>
      <el-form-item label="Name">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="Type">
        <el-select v-model="form.unit_type">
          <el-option label="Group" value="group" />
          <el-option label="Region" value="region" />
          <el-option label="Campus Cluster" value="campus_cluster" />
        </el-select>
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

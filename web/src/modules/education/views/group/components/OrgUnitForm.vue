<script setup lang="ts">
import { saveOrgUnit } from '../../../api/group/org.ts'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean], 'success': [] }>()

const saving = ref(false)
const form = reactive({ code: '', name: '', unit_type: 'group', status: 'enabled' })
const unitTypeOptions = [
  { label: '集团', value: 'group' },
  { label: '区域', value: 'region' },
  { label: '校区集群', value: 'campus_cluster' },
]
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
  <el-dialog v-model="visible" title="组织单元" width="520px">
    <el-form :model="form" label-width="96px">
      <el-form-item label="编码">
        <el-input v-model="form.code" />
      </el-form-item>
      <el-form-item label="名称">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="类型">
        <el-select v-model="form.unit_type">
          <el-option v-for="item in unitTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
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

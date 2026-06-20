<script setup lang="ts">
import { saveTrialStandard } from '../../api/standards/trial-standard.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsTrialStandardEditor' })

const form = reactive({ course_id: 0, standard_code: '', standard_name: '', guardian_visible: false, items: [{ item_name: '', item_content: '', sort_order: 0 }] })
const status = ref('')
const canSave = computed(() => hasAuth('education:standards:trial:save'))

async function save() {
  const response = await saveTrialStandard(form)
  status.value = response.data.status ?? 'draft'
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>试听标准</span></template><el-form label-width="120px"><el-form-item label="课程 ID"><el-input-number v-model="form.course_id" :min="0" /></el-form-item><el-form-item label="标准编码"><el-input v-model="form.standard_code" /></el-form-item><el-form-item label="标准名称"><el-input v-model="form.standard_name" /></el-form-item><el-form-item label="家长可见"><el-switch v-model="form.guardian_visible" /></el-form-item><el-button v-if="canSave" type="primary" @click="save">保存</el-button><el-tag v-if="status">{{ standardStatusLabel(status) }}</el-tag></el-form></el-card></div>
</template>

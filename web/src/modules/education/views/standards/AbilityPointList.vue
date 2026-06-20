<script setup lang="ts">
import { saveAbilityPoint } from '../../api/standards/stage-goal.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsAbilityPointList' })

const form = reactive({ ability_code: '', ability_name: '', ability_group: '' })
const rows = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:ability:save'))

async function save() {
  const response = await saveAbilityPoint(form)
  rows.value.unshift({ ...form, id: response.data.ability_point_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>能力点</span></template><el-form inline><el-input v-model="form.ability_code" placeholder="能力点编码" /><el-input v-model="form.ability_name" placeholder="能力点名称" /><el-input v-model="form.ability_group" placeholder="能力分组" /><el-button v-if="canSave" type="primary" @click="save">保存</el-button></el-form><el-table :data="rows"><el-table-column prop="ability_code" label="编码" /><el-table-column prop="ability_name" label="名称" /></el-table></el-card></div>
</template>

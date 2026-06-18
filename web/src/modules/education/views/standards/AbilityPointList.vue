<script setup lang="ts">
import { saveAbilityPoint } from '../../api/standards/stage-goal.ts'

defineOptions({ name: 'EducationStandardsAbilityPointList' })

const form = reactive({ ability_code: '', ability_name: '', ability_group: '' })
const rows = ref<any[]>([])

async function save() {
  const response = await saveAbilityPoint(form)
  rows.value.unshift({ ...form, id: response.data.ability_point_id })
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Ability Points</span></template><el-form inline><el-input v-model="form.ability_code" placeholder="Code" /><el-input v-model="form.ability_name" placeholder="Name" /><el-input v-model="form.ability_group" placeholder="Group" /><el-button type="primary" @click="save">Save</el-button></el-form><el-table :data="rows"><el-table-column prop="ability_code" label="Code" /><el-table-column prop="ability_name" label="Name" /></el-table></el-card></div>
</template>

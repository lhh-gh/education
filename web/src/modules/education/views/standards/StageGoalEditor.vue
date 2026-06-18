<script setup lang="ts">
import { saveStageGoal } from '../../api/standards/stage-goal.ts'
import { stageGoalPayload } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsStageGoalEditor' })

const form = reactive({ service_package_id: 0, goal_code: '', goal_name: '', goal_content: '', ability_point_ids: [] as number[] })
const saved = ref<any[]>([])

async function save() {
  const response = await saveStageGoal(stageGoalPayload(form))
  saved.value.unshift({ ...form, id: response.data.stage_goal_id })
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header><span>Stage Goals</span></template>
      <el-form label-width="120px">
        <el-form-item label="Package"><el-input-number v-model="form.service_package_id" :min="0" /></el-form-item>
        <el-form-item label="Code"><el-input v-model="form.goal_code" /></el-form-item>
        <el-form-item label="Name"><el-input v-model="form.goal_name" /></el-form-item>
        <el-form-item label="Content"><el-input v-model="form.goal_content" type="textarea" /></el-form-item>
        <el-form-item label="Abilities"><el-select v-model="form.ability_point_ids" multiple><el-option :value="1" label="Line" /><el-option :value="2" label="Color" /></el-select></el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form>
      <el-table :data="saved"><el-table-column prop="goal_name" label="Goal" /><el-table-column prop="ability_point_ids" label="Abilities" /></el-table>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { saveStageGoal } from '../../api/standards/stage-goal.ts'
import { stageGoalPayload } from './standardRules.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsStageGoalEditor' })

const form = reactive({ service_package_id: 0, goal_code: '', goal_name: '', goal_content: '', ability_point_ids: [] as number[] })
const saved = ref<any[]>([])
const canSave = computed(() => hasAuth('education:standards:stage-goal:save'))

async function save() {
  const response = await saveStageGoal(stageGoalPayload(form))
  saved.value.unshift({ ...form, id: response.data.stage_goal_id })
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header><span>阶段目标</span></template>
      <el-form label-width="120px">
        <el-form-item label="服务包 ID"><el-input-number v-model="form.service_package_id" :min="0" /></el-form-item>
        <el-form-item label="目标编码"><el-input v-model="form.goal_code" /></el-form-item>
        <el-form-item label="目标名称"><el-input v-model="form.goal_name" /></el-form-item>
        <el-form-item label="目标内容"><el-input v-model="form.goal_content" type="textarea" /></el-form-item>
        <el-form-item label="能力点"><el-select v-model="form.ability_point_ids" multiple><el-option :value="1" label="线条" /><el-option :value="2" label="色彩" /></el-select></el-form-item>
        <el-button v-if="canSave" type="primary" @click="save">保存</el-button>
      </el-form>
      <el-table :data="saved"><el-table-column prop="goal_name" label="目标" /><el-table-column prop="ability_point_ids" label="能力点" /></el-table>
    </el-card>
  </div>
</template>

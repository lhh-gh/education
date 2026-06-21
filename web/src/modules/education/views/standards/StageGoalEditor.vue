<script setup lang="ts">
import type { StageGoalPayload } from '../../api/standards/stage-goal.ts'
import { saveStageGoal } from '../../api/standards/stage-goal.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { stageGoalPayload } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsStageGoalEditor' })

interface StageGoalRow extends StageGoalPayload {
  id: number
}

const form = reactive<StageGoalPayload>({
  service_package_id: 0,
  goal_code: '',
  goal_name: '',
  goal_content: '',
  ability_point_ids: [],
})
const saved = ref<StageGoalRow[]>([])
const canSave = computed(() => hasAuth('education:standards:stage-goal:save'))

async function save() {
  const response = await saveStageGoal(stageGoalPayload(form))
  saved.value.unshift({ ...form, id: response.data.stage_goal_id })
  form.goal_code = ''
  form.goal_name = ''
  form.goal_content = ''
  form.ability_point_ids = []
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>阶段目标</span>
        </div>
      </template>

      <el-form v-if="canSave" :model="form" label-width="120px" class="save-form">
        <el-form-item label="服务包 ID">
          <el-input-number v-model="form.service_package_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="目标编码">
          <el-input v-model="form.goal_code" clearable placeholder="请输入目标编码" />
        </el-form-item>
        <el-form-item label="目标名称">
          <el-input v-model="form.goal_name" clearable placeholder="请输入目标名称" />
        </el-form-item>
        <el-form-item label="目标内容">
          <el-input v-model="form.goal_content" type="textarea" :rows="3" placeholder="请输入目标内容" />
        </el-form-item>
        <el-form-item label="能力点">
          <el-select v-model="form.ability_point_ids" multiple class="wide-select" placeholder="请选择能力点">
            <el-option :value="1" label="线条控制" />
            <el-option :value="2" label="色彩表达" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            保存阶段目标
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="saved" row-key="id">
        <el-table-column prop="goal_code" label="目标编码" width="150" />
        <el-table-column prop="goal_name" label="目标名称" min-width="180" />
        <el-table-column prop="service_package_id" label="服务包 ID" width="120" />
        <el-table-column prop="goal_content" label="目标内容" min-width="260" show-overflow-tooltip />
        <el-table-column prop="ability_point_ids" label="能力点" width="160" />
        <template #empty>
          <el-empty description="暂无阶段目标" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .save-form {
    margin-bottom: 16px;
  }

  .wide-select {
    width: 260px;
  }
}
</style>

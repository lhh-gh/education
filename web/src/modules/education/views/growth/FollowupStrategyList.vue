<script setup lang="ts">
import { saveFollowupStrategy } from '../../api/growth/followup.ts'

defineOptions({ name: 'EducationGrowthFollowupStrategyList' })

const form = reactive({ strategy_code: '', strategy_name: '', lead_stage: 'followed', score_level: 'hot', suggestion_template: '', next_follow_hours: 4 })
const rows = ref<Array<typeof form & { status: string }>>([])

async function save() {
  const response = await saveFollowupStrategy(form)
  rows.value.unshift({ ...form, status: response.data.status })
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Followup Strategies</span>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.strategy_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.strategy_name" />
        </el-form-item>
        <el-form-item label="Template">
          <el-input v-model="form.suggestion_template" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save">
            Save
          </el-button>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="strategy_code">
        <el-table-column prop="strategy_code" label="Code" width="140" />
        <el-table-column prop="strategy_name" label="Name" min-width="160" />
        <el-table-column prop="score_level" label="Score" width="120" />
        <el-table-column prop="status" label="Status" width="120" />
      </el-table>
    </el-card>
  </div>
</template>

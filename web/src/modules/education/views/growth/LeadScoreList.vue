<script setup lang="ts">
import type { LeadScoreResult } from '../../api/growth/lead-score.ts'
import { recalculateLeadScore } from '../../api/growth/lead-score.ts'

defineOptions({ name: 'EducationGrowthLeadScoreList' })

const leadId = ref<number>()
const rows = ref<LeadScoreResult[]>([])

async function recalculate() {
  if (!leadId.value) {
    return
  }
  const response = await recalculateLeadScore(leadId.value, { reason: 'manual refresh' })
  rows.value = [response.data, ...rows.value.filter(row => row.lead_id !== response.data.lead_id)]
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>Lead Scores</span>
      </template>
      <el-form inline>
        <el-form-item label="Lead ID">
          <el-input-number v-model="leadId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="recalculate">
            Recalculate
          </el-button>
        </el-form-item>
      </el-form>
      <el-table :data="rows" row-key="lead_id">
        <el-table-column prop="lead_id" label="Lead" width="120" />
        <el-table-column prop="score" label="Score" width="120" />
        <el-table-column prop="score_level" label="Level" width="140" />
        <template #empty>
          <el-empty description="No score rows" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

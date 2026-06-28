<script setup lang="ts">
import type { LeadScoreResult } from '../../api/growth/lead-score.ts'
import { recalculateLeadScore } from '../../api/growth/lead-score.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { growthStatusLabel, growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthLeadScoreList' })

const leadId = ref<number>()
const rows = ref<LeadScoreResult[]>([])
const loading = ref(false)
const canRecalculate = computed(() => hasAuth('education:growth:score:recalculate'))

async function recalculate() {
  if (!leadId.value) {
    return
  }
  loading.value = true
  try {
    const response = await recalculateLeadScore(leadId.value, { reason: 'manual refresh' })
    rows.value = [response.data, ...rows.value.filter(row => row.lead_id !== response.data.lead_id)]
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>线索评分</span>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.leadId">
          <el-input-number v-model="leadId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item v-if="canRecalculate">
          <el-button type="primary" @click="recalculate">
            重新计算
          </el-button>
        </el-form-item>
        <el-form-item v-else>
          <el-tag type="info">
            {{ growthText.noPermission }}
          </el-tag>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="lead_id">
        <el-table-column prop="lead_id" :label="growthText.fields.lead" width="120" />
        <el-table-column prop="score" :label="growthText.fields.score" width="120" />
        <el-table-column :label="growthText.fields.level" width="140">
          <template #default="{ row }">
            {{ growthStatusLabel(row.score_level) }}
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无评分记录" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

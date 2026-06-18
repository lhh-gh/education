<script setup lang="ts">
import type { GrowthHotLead, GrowthSuggestion } from '../../api/growth/workbench.ts'
import { getGrowthWorkbench } from '../../api/growth/workbench.ts'
import FollowupSuggestionPanel from './components/FollowupSuggestionPanel.vue'
import { growthWorkbenchFilterPayload } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthWorkbench' })

const loading = ref(false)
const filters = reactive({ tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, owner_user_id: undefined as number | undefined, score_level: '' })
const hotLeads = ref<GrowthHotLead[]>([])
const suggestions = ref<GrowthSuggestion[]>([])

async function loadWorkbench() {
  loading.value = true
  try {
    const response = await getGrowthWorkbench(growthWorkbenchFilterPayload(filters))
    hotLeads.value = response.data.hot_leads
    suggestions.value = response.data.suggestions
  }
  finally {
    loading.value = false
  }
}

function markSuggestion(row: GrowthSuggestion, status: 'accepted' | 'ignored') {
  row.status = status
}

onMounted(loadWorkbench)
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Growth Workbench</span>
          <el-button type="primary" @click="loadWorkbench">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Owner">
          <el-input-number v-model="filters.owner_user_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Level">
          <el-select v-model="filters.score_level" clearable style="width: 150px;">
            <el-option label="Hot" value="hot" />
            <el-option label="High" value="high" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="hotLeads" row-key="lead_id">
        <el-table-column prop="lead_id" label="Lead" width="100" />
        <el-table-column prop="score" label="Score" width="100" />
        <el-table-column prop="score_level" label="Level" width="120" />
        <el-table-column prop="summary" label="Summary" min-width="220" />
        <template #empty>
          <el-empty description="No hot leads" />
        </template>
      </el-table>
    </el-card>
    <el-card class="mt-3" shadow="never">
      <template #header>
        <span>Followup Suggestions</span>
      </template>
      <FollowupSuggestionPanel :suggestions="suggestions" @accept="markSuggestion($event, 'accepted')" @ignore="markSuggestion($event, 'ignored')" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-growth-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
  }
}
</style>

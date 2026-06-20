<script setup lang="ts">
import type { GrowthHotLead, GrowthSuggestion } from '../../api/growth/workbench.ts'
import { getGrowthWorkbench } from '../../api/growth/workbench.ts'
import FollowupSuggestionPanel from './components/FollowupSuggestionPanel.vue'
import { growthStatusLabel, growthText, growthWorkbenchFilterPayload } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthWorkbench' })

const loading = ref(false)
const filters = reactive({ owner_user_id: undefined as number | undefined, score_level: '' })
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
          <span>{{ growthText.workbenchTitle }}</span>
          <el-button type="primary" @click="loadWorkbench">
            {{ growthText.refresh }}
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.owner">
          <el-input-number v-model="filters.owner_user_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item :label="growthText.fields.level">
          <el-select v-model="filters.score_level" clearable style="width: 150px;">
            <el-option :label="growthText.levels.hot" value="hot" />
            <el-option :label="growthText.levels.high" value="high" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="hotLeads" row-key="lead_id">
        <el-table-column prop="lead_id" :label="growthText.fields.lead" width="100" />
        <el-table-column prop="score" :label="growthText.fields.score" width="100" />
        <el-table-column :label="growthText.fields.level" width="120">
          <template #default="{ row }">
            {{ growthStatusLabel(row.score_level) }}
          </template>
        </el-table-column>
        <el-table-column prop="summary" :label="growthText.fields.summary" min-width="220" />
        <template #empty>
          <el-empty :description="growthText.empty.hotLeads" />
        </template>
      </el-table>
    </el-card>
    <el-card class="mt-3" shadow="never">
      <template #header>
        <span>跟进建议</span>
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

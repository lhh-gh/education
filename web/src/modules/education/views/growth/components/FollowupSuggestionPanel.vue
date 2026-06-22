<script setup lang="ts">
import type { GrowthSuggestion } from '../../../api/growth/workbench.ts'
import { growthText, suggestionActionState } from '../growthRules.ts'

defineOptions({ name: 'EducationGrowthFollowupSuggestionPanel' })

defineProps<{ suggestions: GrowthSuggestion[] }>()
const emit = defineEmits<{ accept: [suggestion: GrowthSuggestion], ignore: [suggestion: GrowthSuggestion] }>()
</script>

<template>
  <el-table :data="suggestions" row-key="id">
    <el-table-column prop="lead_id" :label="growthText.fields.lead" width="100" />
    <el-table-column prop="suggestion_text" label="跟进建议" min-width="220" />
    <el-table-column prop="due_at" label="截止时间" width="180" />
    <el-table-column :label="growthText.fields.actions" width="170">
      <template #default="{ row }">
        <el-button link type="primary" :disabled="suggestionActionState(row).disabled" @click="emit('accept', row)">
          {{ suggestionActionState(row).label || '采纳' }}
        </el-button>
        <el-button link type="info" :disabled="row.status !== 'pending'" @click="emit('ignore', row)">
          {{ growthText.ignore }}
        </el-button>
      </template>
    </el-table-column>
    <template #empty>
      <el-empty :description="growthText.empty.suggestions" />
    </template>
  </el-table>
</template>

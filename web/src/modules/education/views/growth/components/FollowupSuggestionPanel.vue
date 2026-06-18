<script setup lang="ts">
import type { GrowthSuggestion } from '../../../api/growth/workbench.ts'
import { suggestionActionState } from '../growthRules.ts'

defineOptions({ name: 'EducationGrowthFollowupSuggestionPanel' })

defineProps<{ suggestions: GrowthSuggestion[] }>()
const emit = defineEmits<{ accept: [suggestion: GrowthSuggestion], ignore: [suggestion: GrowthSuggestion] }>()
</script>

<template>
  <el-table :data="suggestions" row-key="id">
    <el-table-column prop="lead_id" label="Lead" width="100" />
    <el-table-column prop="suggestion_text" label="Suggestion" min-width="220" />
    <el-table-column prop="due_at" label="Due" width="180" />
    <el-table-column label="Actions" width="170">
      <template #default="{ row }">
        <el-button link type="primary" :disabled="suggestionActionState(row).disabled" @click="emit('accept', row)">
          {{ suggestionActionState(row).label }}
        </el-button>
        <el-button link type="info" :disabled="row.status !== 'pending'" @click="emit('ignore', row)">
          Ignore
        </el-button>
      </template>
    </el-table-column>
    <template #empty>
      <el-empty description="No followup suggestions" />
    </template>
  </el-table>
</template>

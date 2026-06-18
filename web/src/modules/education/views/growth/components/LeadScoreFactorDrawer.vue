<script setup lang="ts">
defineOptions({ name: 'EducationGrowthLeadScoreFactorDrawer' })

defineProps<{
  modelValue: boolean
  factors: Array<{ factor_code: string, factor_name: string, points: number, factor_value?: string }>
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
</script>

<template>
  <el-drawer :model-value="modelValue" title="Score Factors" size="420px" @update:model-value="emit('update:modelValue', $event)">
    <el-table :data="factors" row-key="factor_code">
      <el-table-column prop="factor_name" label="Factor" min-width="140" />
      <el-table-column prop="factor_value" label="Value" width="120" />
      <el-table-column prop="points" label="Points" width="90" />
      <template #empty>
        <el-empty description="No score factors" />
      </template>
    </el-table>
  </el-drawer>
</template>

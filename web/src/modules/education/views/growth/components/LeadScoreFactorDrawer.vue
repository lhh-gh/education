<script setup lang="ts">
defineOptions({ name: 'EducationGrowthLeadScoreFactorDrawer' })

defineProps<{
  modelValue: boolean
  factors: Array<{ factor_code: string, factor_name: string, points: number, factor_value?: string }>
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
</script>

<template>
  <el-drawer :model-value="modelValue" title="评分因子" size="420px" @update:model-value="emit('update:modelValue', $event)">
    <el-table :data="factors" row-key="factor_code">
      <el-table-column prop="factor_name" label="因子名称" min-width="140" />
      <el-table-column prop="factor_value" label="因子值" width="120" />
      <el-table-column prop="points" label="分值" width="90" />
      <template #empty>
        <el-empty description="暂无评分因子" />
      </template>
    </el-table>
  </el-drawer>
</template>

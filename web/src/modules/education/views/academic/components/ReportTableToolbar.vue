<script setup lang="ts">
defineOptions({ name: 'EducationReportTableToolbar' })

withDefaults(defineProps<{
  title: string
  total?: number
  loading?: boolean
}>(), {
  total: 0,
  loading: false,
})

const emit = defineEmits<{
  (event: 'refresh'): void
}>()
</script>

<template>
  <div class="report-table-toolbar">
    <div class="toolbar-title">
      <span>{{ title }}</span>
      <el-tag size="small" type="info">
        {{ total }}
      </el-tag>
    </div>
    <div class="toolbar-actions">
      <slot name="export" />
      <el-button :loading="loading" @click="emit('refresh')">
        Refresh
      </el-button>
    </div>
  </div>
</template>

<style scoped lang="scss">
.report-table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 40px;
  margin-bottom: 12px;

  .toolbar-title,
  .toolbar-actions {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .toolbar-title {
    color: var(--el-text-color-primary);
    font-weight: 600;
  }
}
</style>

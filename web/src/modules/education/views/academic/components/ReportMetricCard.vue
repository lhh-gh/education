<script setup lang="ts">
defineOptions({ name: 'EducationReportMetricCard' })

withDefaults(defineProps<{
  title: string
  value: string | number
  unit?: string
  trend?: string
  status?: 'default' | 'success' | 'warning' | 'danger'
}>(), {
  unit: '',
  trend: '',
  status: 'default',
})
</script>

<template>
  <div class="report-metric-card" :class="`is-${status}`">
    <div class="metric-title">
      {{ title }}
    </div>
    <div class="metric-value-row">
      <span class="metric-value">{{ value }}</span>
      <span v-if="unit" class="metric-unit">{{ unit }}</span>
    </div>
    <div class="metric-trend">
      {{ trend || '&nbsp;' }}
    </div>
  </div>
</template>

<style scoped lang="scss">
.report-metric-card {
  min-width: 180px;
  min-height: 104px;
  padding: 16px;
  overflow: hidden;
  background: var(--el-bg-color);
  border: 1px solid var(--el-border-color-light);
  border-radius: 8px;

  .metric-title {
    min-height: 20px;
    font-size: 13px;
    line-height: 20px;
    color: var(--el-text-color-secondary);
  }

  .metric-value-row {
    display: flex;
    align-items: baseline;
    min-height: 40px;
    margin-top: 8px;
  }

  .metric-value {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 26px;
    font-weight: 650;
    line-height: 34px;
    color: var(--el-text-color-primary);
    white-space: nowrap;
  }

  .metric-unit {
    flex: none;
    margin-left: 6px;
    font-size: 13px;
    color: var(--el-text-color-secondary);
  }

  .metric-trend {
    min-height: 18px;
    margin-top: 4px;
    font-size: 12px;
    line-height: 18px;
    color: var(--el-text-color-secondary);
  }

  &.is-success {
    border-color: var(--el-color-success-light-7);
  }

  &.is-warning {
    border-color: var(--el-color-warning-light-7);
  }

  &.is-danger {
    border-color: var(--el-color-danger-light-7);
  }
}
</style>

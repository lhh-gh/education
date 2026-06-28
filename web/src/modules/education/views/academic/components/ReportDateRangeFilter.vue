<script setup lang="ts">
import type { ReportDateRangeValue } from '../reportRules.ts'
import { quickReportRange, validateReportDateRange } from '../reportRules.ts'

defineOptions({ name: 'EducationReportDateRangeFilter' })

const props = withDefaults(defineProps<{
  modelValue: ReportDateRangeValue
  requireRange?: boolean
  maxDays?: number
}>(), {
  requireRange: false,
  maxDays: 366,
})

const emit = defineEmits<{
  (event: 'update:modelValue', value: ReportDateRangeValue): void
  (event: 'submit', value: ReportDateRangeValue): void
  (event: 'reset'): void
}>()

const localValue = reactive<ReportDateRangeValue>({ ...props.modelValue })
const errorText = ref('')

watch(() => props.modelValue, (value) => {
  Object.assign(localValue, value)
}, { deep: true })

function updateField<Key extends keyof ReportDateRangeValue>(key: Key, value: ReportDateRangeValue[Key]) {
  localValue[key] = value
  emit('update:modelValue', { ...localValue })
}

function applyQuickRange(type: 'today' | 'this_week' | 'this_month') {
  Object.assign(localValue, quickReportRange(type))
  errorText.value = ''
  emit('update:modelValue', { ...localValue })
}

function handleSubmit() {
  errorText.value = validateReportDateRange(localValue, props.requireRange, props.maxDays)
  if (!errorText.value) {
    emit('submit', { ...localValue })
  }
}

function handleReset() {
  errorText.value = ''
  emit('reset')
}
</script>

<template>
  <div class="report-date-range-filter">
    <el-alert v-if="errorText" class="mb-3" type="warning" show-icon :closable="false" :title="errorText" />
    <el-form :inline="true" :model="localValue" class="report-filter-form">
      <el-form-item label="开始时间">
        <el-date-picker :model-value="localValue.start_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" @update:model-value="updateField('start_at', $event || undefined)" />
      </el-form-item>
      <el-form-item label="结束时间">
        <el-date-picker :model-value="localValue.end_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" @update:model-value="updateField('end_at', $event || undefined)" />
      </el-form-item>
      <el-form-item>
        <el-button-group>
          <el-button @click="applyQuickRange('today')">
            今天
          </el-button>
          <el-button @click="applyQuickRange('this_week')">
            本周
          </el-button>
          <el-button @click="applyQuickRange('this_month')">
            本月
          </el-button>
        </el-button-group>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleSubmit">
          查询
        </el-button>
        <el-button @click="handleReset">
          重置
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<style scoped lang="scss">
.report-date-range-filter {
  width: 100%;

  .report-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 0 8px;
    align-items: flex-start;
  }
}
</style>

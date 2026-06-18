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
      <el-form-item label="Tenant ID">
        <el-input-number :model-value="localValue.tenant_id" :min="1" :controls="false" @update:model-value="updateField('tenant_id', $event || undefined)" />
      </el-form-item>
      <el-form-item label="Campus ID">
        <el-input-number :model-value="localValue.campus_id" :min="1" :controls="false" @update:model-value="updateField('campus_id', $event || undefined)" />
      </el-form-item>
      <el-form-item label="Start">
        <el-date-picker :model-value="localValue.start_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" @update:model-value="updateField('start_at', $event || undefined)" />
      </el-form-item>
      <el-form-item label="End">
        <el-date-picker :model-value="localValue.end_at" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" @update:model-value="updateField('end_at', $event || undefined)" />
      </el-form-item>
      <el-form-item>
        <el-button-group>
          <el-button @click="applyQuickRange('today')">
            Today
          </el-button>
          <el-button @click="applyQuickRange('this_week')">
            This Week
          </el-button>
          <el-button @click="applyQuickRange('this_month')">
            This Month
          </el-button>
        </el-button-group>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleSubmit">
          Search
        </el-button>
        <el-button @click="handleReset">
          Reset
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

<script setup lang="ts">
import type { ReportState } from '../reportRules.ts'
import { reportStateMessage, shouldEmitRetry } from '../reportRules.ts'

defineOptions({ name: 'EducationReportStateBlock' })

const props = withDefaults(defineProps<{
  state: ReportState
  message?: string
  retryText?: string
}>(), {
  message: '',
  retryText: 'Retry',
})

const emit = defineEmits<{
  (event: 'retry'): void
}>()

const displayMessage = computed(() => reportStateMessage(props.state, props.message))
const showRetry = computed(() => shouldEmitRetry(props.state))
</script>

<template>
  <div class="report-state-block" :class="`is-${state}`">
    <el-skeleton v-if="state === 'loading'" :rows="3" animated />
    <template v-else>
      <el-empty :description="displayMessage">
        <el-button v-if="showRetry" type="primary" @click="emit('retry')">
          {{ retryText }}
        </el-button>
      </el-empty>
    </template>
  </div>
</template>

<style scoped lang="scss">
.report-state-block {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  padding: 24px;
  border: 1px dashed var(--el-border-color);
  border-radius: 8px;
  background: var(--el-fill-color-blank);
}
</style>

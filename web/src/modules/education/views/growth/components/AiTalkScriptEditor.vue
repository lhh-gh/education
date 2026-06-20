<script setup lang="ts">
import { containsBlockedAiPromise } from '../growthRules.ts'
import { growthText } from '../growthRules.ts'

defineOptions({ name: 'EducationGrowthAiTalkScriptEditor' })

const props = withDefaults(defineProps<{ modelValue: string, canConfirm?: boolean }>(), {
  canConfirm: true,
})
const emit = defineEmits<{ 'update:modelValue': [value: string], 'confirm': [value: string] }>()

const localValue = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})
const blocked = computed(() => containsBlockedAiPromise(localValue.value))
</script>

<template>
  <div class="growth-script-editor">
    <el-input v-model="localValue" type="textarea" :rows="8" :placeholder="growthText.scriptPlaceholder" />
    <div class="editor-actions">
      <el-alert v-if="blocked" type="error" show-icon :closable="false" :title="growthText.aiBlocked" />
      <el-button v-if="canConfirm" type="primary" :disabled="blocked || !localValue.trim()" @click="emit('confirm', localValue)">
        {{ growthText.confirm }}
      </el-button>
      <el-tag v-else type="info">
        {{ growthText.noPermission }}
      </el-tag>
    </div>
  </div>
</template>

<style scoped lang="scss">
.growth-script-editor {
  .editor-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
    margin-top: 12px;
  }
}
</style>

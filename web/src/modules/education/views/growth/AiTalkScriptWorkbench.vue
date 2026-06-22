<script setup lang="ts">
import { confirmAiTalkScript, generateAiTalkScript } from '../../api/growth/ai-script.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AiTalkScriptEditor from './components/AiTalkScriptEditor.vue'
import { aiScriptPayload, containsBlockedAiPromise, growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthAiTalkScriptWorkbench' })

const form = reactive({ lead_id: undefined as number | undefined, script_type: 'trial_invitation', goal: '', generated_text: '' })
const scriptId = ref<number>()
const scriptText = ref('')
const loading = ref(false)
const blocked = computed(() => containsBlockedAiPromise(form.generated_text))
const canGenerate = computed(() => hasAuth('education:growth:ai-script:generate'))
const canConfirm = computed(() => hasAuth('education:growth:ai-script:confirm'))

async function generate() {
  if (!form.lead_id || blocked.value) {
    return
  }
  loading.value = true
  try {
    const response = await generateAiTalkScript(aiScriptPayload({ lead_id: form.lead_id, script_type: form.script_type, goal: form.goal, generated_text: form.generated_text || undefined }))
    scriptId.value = response.data.ai_talk_script_id
    scriptText.value = form.generated_text || growthText.generatedFallback
  }
  finally {
    loading.value = false
  }
}

async function confirm(value: string) {
  if (!scriptId.value) {
    return
  }
  await confirmAiTalkScript(scriptId.value, { edited_script: value })
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>AI 话术</span>
      </template>
      <el-form label-width="120px">
        <el-form-item :label="growthText.fields.leadId">
          <el-input-number v-model="form.lead_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item :label="growthText.fields.scriptType">
          <el-input v-model="form.script_type" />
        </el-form-item>
        <el-form-item :label="growthText.fields.goal">
          <el-input v-model="form.goal" />
        </el-form-item>
        <el-form-item :label="growthText.fields.draftText">
          <el-input v-model="form.generated_text" type="textarea" :rows="4" />
        </el-form-item>
        <el-form-item v-if="blocked">
          <el-alert type="error" show-icon :closable="false" title="AI 不允许承诺自动优惠" />
        </el-form-item>
        <el-form-item>
          <el-button v-if="canGenerate" type="primary" :loading="loading" :disabled="blocked" @click="generate">
            生成话术
          </el-button>
          <el-tag v-else type="info">
            {{ growthText.noPermission }}
          </el-tag>
        </el-form-item>
      </el-form>
      <AiTalkScriptEditor v-model="scriptText" :can-confirm="canConfirm" @confirm="confirm" />
    </el-card>
  </div>
</template>

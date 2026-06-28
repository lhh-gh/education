<script setup lang="ts">
import { getTrialConversionLink } from '../../api/growth/trial-conversion.ts'
import { growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthTrialConversionList' })

const leadId = ref<number>()
const conversionOwner = ref('')
const loading = ref(false)

async function loadLink() {
  if (!leadId.value) {
    return
  }
  loading.value = true
  try {
    const response = await getTrialConversionLink(leadId.value)
    conversionOwner.value = response.data.conversion_owner
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>试听转化</span>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.leadId">
          <el-input-number v-model="leadId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="loading" @click="loadLink">
            加载转化链路
          </el-button>
        </el-form-item>
      </el-form>
      <el-result v-if="conversionOwner" icon="success" title="转化链路" :sub-title="`${growthText.ownerPrefix}: ${conversionOwner}`" />
      <el-empty v-else description="暂无转化链路" />
    </el-card>
  </div>
</template>

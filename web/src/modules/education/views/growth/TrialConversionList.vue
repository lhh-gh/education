<script setup lang="ts">
import { getTrialConversionLink } from '../../api/growth/trial-conversion.ts'
import { growthText } from './growthRules.ts'

defineOptions({ name: 'EducationGrowthTrialConversionList' })

const leadId = ref<number>()
const conversionOwner = ref('')

async function loadLink() {
  if (!leadId.value) {
    return
  }
  const response = await getTrialConversionLink(leadId.value)
  conversionOwner.value = response.data.conversion_owner
}
</script>

<template>
  <div class="mine-layout education-growth-page pt-3">
    <el-card shadow="never">
      <template #header>
        <span>{{ growthText.trialConversionTitle }}</span>
      </template>
      <el-form inline>
        <el-form-item :label="growthText.fields.leadId">
          <el-input-number v-model="leadId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadLink">
            {{ growthText.loadConversion }}
          </el-button>
        </el-form-item>
      </el-form>
      <el-result v-if="conversionOwner" icon="success" :title="growthText.conversionFlow" :sub-title="`${growthText.ownerPrefix}: ${conversionOwner}`" />
      <el-empty v-else :description="growthText.empty.conversion" />
    </el-card>
  </div>
</template>

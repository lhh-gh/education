<script setup lang="ts">
import { getTrialConversionLink } from '../../api/growth/trial-conversion.ts'

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
        <span>Trial Conversion</span>
      </template>
      <el-form inline>
        <el-form-item label="Lead ID">
          <el-input-number v-model="leadId" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadLink">
            Load V3 Link
          </el-button>
        </el-form-item>
      </el-form>
      <el-result v-if="conversionOwner" icon="success" title="Conversion Flow" :sub-title="`Owned by ${conversionOwner}`" />
      <el-empty v-else description="No conversion selected" />
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { reviewStandardVersion } from '../../api/standards/version.ts'

defineOptions({ name: 'EducationStandardsStandardReviewList' })

const reviewId = ref<number>()

async function approve() {
  if (reviewId.value) {
    await reviewStandardVersion(reviewId.value, { status: 'approved', review_note: 'ok' })
  }
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>Standard Reviews</span></template><el-form inline><el-input-number v-model="reviewId" :min="1" /><el-button type="primary" @click="approve">Approve</el-button></el-form></el-card></div>
</template>

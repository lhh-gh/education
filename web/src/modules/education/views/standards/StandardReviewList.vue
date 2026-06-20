<script setup lang="ts">
import { reviewStandardVersion } from '../../api/standards/version.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsStandardReviewList' })

const reviewId = ref<number>()
const canReview = computed(() => hasAuth('education:standards:review:handle'))

async function approve() {
  if (reviewId.value) {
    await reviewStandardVersion(reviewId.value, { status: 'approved', review_note: 'ok' })
  }
}
</script>

<template>
  <div class="mine-layout pt-3"><el-card shadow="never"><template #header><span>标准评审</span></template><el-form inline><el-form-item label="评审 ID"><el-input-number v-model="reviewId" :min="1" /></el-form-item><el-button v-if="canReview" type="primary" @click="approve">通过</el-button></el-form></el-card></div>
</template>

<script setup lang="ts">
import type { ContentReviewRow, ContentReviewStatus } from '../../api/content/types.ts'
import { pageContentReviews, reviewContent } from '../../api/content/review.ts'
import { reviewSubmitState } from './contentRules.ts'

defineOptions({ name: 'EducationContentContentReviewList' })

const loading = ref(false)
const rows = ref<ContentReviewRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: 'pending' })
const reviewForm = reactive<{ status?: ContentReviewStatus, review_note?: string }>({ status: undefined, review_note: '' })
const canReview = computed(() => hasAuth('education:content:review:handle'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageContentReviews(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function handle(row: ContentReviewRow, status: Exclude<ContentReviewStatus, 'pending'>) {
  reviewForm.status = status
  if (reviewSubmitState(reviewForm).disabled) {
    return
  }
  await reviewContent(row.id, { status, review_note: reviewForm.review_note })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>内容审核</span>
      </template>
      <el-alert v-if="reviewSubmitState(reviewForm).disabled" class="mb-3" :title="reviewSubmitState(reviewForm).message" type="warning" :closable="false" />
      <el-input v-model="reviewForm.review_note" class="mb-3" type="textarea" :rows="2" placeholder="审核意见" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="business_type" label="业务类型" width="160" />
        <el-table-column prop="business_id" label="业务 ID" width="130" />
        <el-table-column prop="status" label="审核状态" width="130" />
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button v-if="canReview" link type="success" @click="handle(row, 'approved')">
              通过
            </el-button>
            <el-button v-if="canReview" link type="danger" @click="handle(row, 'rejected')">
              驳回
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无审核记录" />
        </template>
      </el-table>
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
  </div>
</template>

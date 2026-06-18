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
        <span>Content Reviews</span>
      </template>
      <el-alert v-if="reviewSubmitState(reviewForm).disabled" class="mb-3" :title="reviewSubmitState(reviewForm).message" type="warning" :closable="false" />
      <el-input v-model="reviewForm.review_note" class="mb-3" type="textarea" :rows="2" placeholder="Review note" />
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="business_type" label="Business" width="160" />
        <el-table-column prop="business_id" label="Business ID" width="130" />
        <el-table-column prop="status" label="Status" width="130" />
        <el-table-column label="Actions" width="180">
          <template #default="{ row }">
            <el-button link type="success" @click="handle(row, 'approved')">
              Approve
            </el-button>
            <el-button link type="danger" @click="handle(row, 'rejected')">
              Reject
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No review records" />
        </template>
      </el-table>
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
  </div>
</template>

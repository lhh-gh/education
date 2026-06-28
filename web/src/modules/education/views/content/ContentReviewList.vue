<script setup lang="ts">
import type { ContentReviewRow, ContentReviewStatus } from '../../api/content/types.ts'
import { pageContentReviews, reviewContent } from '../../api/content/review.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { contentReviewStatusOptions, contentStatusLabel, contentStatusTag, reviewSubmitState } from './contentRules.ts'

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
  reviewForm.review_note = ''
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>内容审核</span>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="审核状态">
          <el-select v-model="search.status" clearable class="filter-select">
            <el-option v-for="item in contentReviewStatusOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核意见">
          <el-input v-model="reviewForm.review_note" clearable placeholder="请输入审核意见" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>

      <el-alert v-if="reviewSubmitState(reviewForm).disabled" class="mb-3" :title="reviewSubmitState(reviewForm).message" type="warning" :closable="false" />

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="business_type" label="业务类型" width="160" />
        <el-table-column prop="business_id" label="业务 ID" width="130" />
        <el-table-column prop="reviewer_id" label="审核人 ID" width="130" />
        <el-table-column label="审核状态" width="130">
          <template #default="{ row }">
            <el-tag :type="contentStatusTag(row.status)">
              {{ contentStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_note" label="审核意见" min-width="180" show-overflow-tooltip />
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

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadRows"
      />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .filter-select {
    width: 140px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

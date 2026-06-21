<script setup lang="ts">
import { reviewStandardVersion } from '../../api/standards/version.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { standardStatusLabel } from './standardRules.ts'

defineOptions({ name: 'EducationStandardsStandardReviewList' })

interface ReviewRow {
  id: number
  status: 'approved' | 'rejected'
  review_note: string
}

const form = reactive({
  review_id: undefined as number | undefined,
  review_note: '',
})
const rows = ref<ReviewRow[]>([])
const canReview = computed(() => hasAuth('education:standards:review:handle'))

async function review(status: 'approved' | 'rejected') {
  if (!form.review_id) {
    return
  }
  await reviewStandardVersion(form.review_id, { status, review_note: form.review_note })
  rows.value.unshift({ id: form.review_id, status, review_note: form.review_note })
  form.review_note = ''
}
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>标准评审</span>
        </div>
      </template>

      <el-form v-if="canReview" :inline="true" :model="form" class="save-form">
        <el-form-item label="评审 ID">
          <el-input-number v-model="form.review_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="评审意见">
          <el-input v-model="form.review_note" clearable placeholder="请输入评审意见" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="review('approved')">
            通过
          </el-button>
          <el-button type="danger" @click="review('rejected')">
            驳回
          </el-button>
        </el-form-item>
      </el-form>

      <el-table :data="rows" row-key="id">
        <el-table-column prop="id" label="评审 ID" width="120" />
        <el-table-column label="评审结果" width="120">
          <template #default="{ row }">
            <el-tag :type="row.status === 'approved' ? 'success' : 'danger'">
              {{ standardStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_note" label="评审意见" min-width="240" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无评审记录" />
        </template>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-standards-page {
  .save-form {
    margin-bottom: 16px;
  }
}
</style>

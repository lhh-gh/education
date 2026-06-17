<script setup lang="ts">
import type { SalaryReviewRecord } from '../../api/payroll/slip.ts'
import { approveSalaryBatch, rejectSalaryBatch } from '../../api/payroll/batch.ts'
import { pageSalaryReviews } from '../../api/payroll/slip.ts'
import { payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollSalaryReviewList' })

const loading = ref(false)
const rows = ref<SalaryReviewRecord[]>([])
const total = ref(0)
const reviewNote = ref('')
const search = reactive({ page: 1, pageSize: 20, status: '', salary_month: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSalaryReviews(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function approve(row: SalaryReviewRecord) {
  await approveSalaryBatch(row.batch_id, { ...search, review_note: reviewNote.value })
  await loadRows()
}

async function reject(row: SalaryReviewRecord) {
  await rejectSalaryBatch(row.batch_id, { ...search, review_note: reviewNote.value })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Salary Reviews</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Status">
          <el-input v-model="search.status" clearable />
        </el-form-item>
        <el-form-item label="Note">
          <el-input v-model="reviewNote" clearable />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="batch_id" label="Batch ID" width="120" />
        <el-table-column prop="reviewer_id" label="Reviewer" width="120" />
        <el-table-column label="Status" width="130">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_note" label="Note" min-width="180" />
        <el-table-column label="Actions" width="170" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.status !== 'submitted'" @click="approve(row)">
              Approve
            </el-button>
            <el-button link type="danger" :disabled="row.status !== 'submitted'" @click="reject(row)">
              Reject
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<script setup lang="ts">
import type { WorkloadDisputeRecord } from '../../api/payroll/dispute.ts'
import { pageWorkloadDisputes } from '../../api/payroll/dispute.ts'
import WorkloadDisputeReviewDrawer from './components/WorkloadDisputeReviewDrawer.vue'
import { payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollWorkloadDisputeList' })

const loading = ref(false)
const rows = ref<WorkloadDisputeRecord[]>([])
const total = ref(0)
const current = ref<WorkloadDisputeRecord | null>(null)
const reviewVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, teacher_id: undefined as number | undefined, status: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageWorkloadDisputes(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function openReview(row: WorkloadDisputeRecord) {
  current.value = row
  reviewVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Workload Disputes</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Teacher ID">
          <el-input-number v-model="search.teacher_id" :min="1" />
        </el-form-item>
        <el-form-item label="Status">
          <el-input v-model="search.status" clearable />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="teacher_name" label="Teacher" min-width="140" />
        <el-table-column prop="salary_month" label="Month" width="120" />
        <el-table-column prop="reason" label="Reason" min-width="220" />
        <el-table-column label="Status" width="130">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="120" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.status !== 'pending'" @click="openReview(row)">
              Review
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <WorkloadDisputeReviewDrawer v-model="reviewVisible" :row="current" @success="loadRows" />
  </div>
</template>

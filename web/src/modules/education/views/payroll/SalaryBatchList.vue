<script setup lang="ts">
import type { SalaryBatchRecord } from '../../api/payroll/batch.ts'
import { pageSalaryBatches, rebuildSalaryBatch, submitSalaryBatchReview } from '../../api/payroll/batch.ts'
import SalaryBatchCalculateForm from './components/SalaryBatchCalculateForm.vue'
import { canRebuildBatch, centsToYuan, payrollConflictText, payrollStatusLabel, payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollSalaryBatchList' })

const loading = ref(false)
const rows = ref<SalaryBatchRecord[]>([])
const total = ref(0)
const calculateVisible = ref(false)
const conflictText = ref('')
const search = reactive({ page: 1, pageSize: 20, salary_month: '', status: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSalaryBatches(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function rebuild(row: SalaryBatchRecord) {
  try {
    await rebuildSalaryBatch(row.id, search)
    await loadRows()
  }
  catch (error: any) {
    conflictText.value = payrollConflictText(error?.message)
  }
}

async function submit(row: SalaryBatchRecord) {
  await submitSalaryBatchReview(row.id, search)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>薪酬批次</span>
          <el-button type="primary" @click="calculateVisible = true">
            计算薪酬
          </el-button>
        </div>
      </template>
      <el-alert v-if="conflictText" type="warning" show-icon :closable="true" :title="conflictText" @close="conflictText = ''" />
      <el-form :inline="true" :model="search" class="search-form mt-3">
        <el-form-item label="月份">
          <el-date-picker v-model="search.salary_month" type="month" value-format="YYYY-MM" />
        </el-form-item>
        <el-form-item label="状态">
          <el-input v-model="search.status" clearable />
        </el-form-item>
        <el-form-item>
          <el-button @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="batch_no" label="批次号" min-width="180" />
        <el-table-column prop="salary_month" label="月份" width="120" />
        <el-table-column prop="teacher_count" label="教师数" width="100" />
        <el-table-column label="应发金额" width="140">
          <template #default="{ row }">
            {{ centsToYuan(row.payable_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ payrollStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="190" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!canRebuildBatch(row)" @click="rebuild(row)">
              重算
            </el-button>
            <el-button link type="primary" :disabled="row.status !== 'calculated'" @click="submit(row)">
              提交
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无薪酬批次" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <SalaryBatchCalculateForm v-model="calculateVisible" @success="loadRows" />
  </div>
</template>

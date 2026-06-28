<script setup lang="ts">
import type { SalarySlipRecord } from '../../api/payroll/slip.ts'
import { pageSalarySlips } from '../../api/payroll/slip.ts'
import SalaryAdjustmentForm from './components/SalaryAdjustmentForm.vue'
import SalarySlipDetailDrawer from './components/SalarySlipDetailDrawer.vue'
import { centsToYuan, payrollStatusLabel, payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollSalarySlipList' })

const loading = ref(false)
const rows = ref<SalarySlipRecord[]>([])
const total = ref(0)
const current = ref<SalarySlipRecord | null>(null)
const detailVisible = ref(false)
const adjustmentVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, teacher_id: undefined as number | undefined, salary_month: '', status: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSalarySlips(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function openDetail(row: SalarySlipRecord) {
  current.value = row
  detailVisible.value = true
}

function openAdjustment(row: SalarySlipRecord) {
  current.value = row
  adjustmentVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>工资条</span>
          <el-button type="primary" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="月份">
          <el-date-picker v-model="search.salary_month" type="month" value-format="YYYY-MM" />
        </el-form-item>
        <el-form-item label="教师 ID">
          <el-input-number v-model="search.teacher_id" :min="1" />
        </el-form-item>
        <el-form-item label="状态">
          <el-input v-model="search.status" clearable />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="teacher_name" label="教师" min-width="150" />
        <el-table-column prop="salary_month" label="月份" width="120" />
        <el-table-column label="应发" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.gross_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="实发" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.payable_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ payrollStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="170" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="openDetail(row)">
              详情
            </el-button>
            <el-button link type="primary" :disabled="row.status !== 'approved'" @click="openAdjustment(row)">
              调整
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无工资条" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <SalarySlipDetailDrawer v-model="detailVisible" :row="current" />
    <SalaryAdjustmentForm v-model="adjustmentVisible" :row="current" @success="loadRows" />
  </div>
</template>

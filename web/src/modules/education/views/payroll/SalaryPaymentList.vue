<script setup lang="ts">
import type { SalaryPaymentPayload, SalaryPaymentRecord } from '../../api/payroll/payment.ts'
import { createSalaryPayment, pageSalaryPayments } from '../../api/payroll/payment.ts'
import { centsToYuan, payrollStatusLabel, payrollTagType } from './payrollRules.ts'

defineOptions({ name: 'EducationPayrollSalaryPaymentList' })

const loading = ref(false)
const rows = ref<SalaryPaymentRecord[]>([])
const total = ref(0)
const formVisible = ref(false)
const form = reactive<SalaryPaymentPayload>({ slip_id: 0, amount_cents: 0 })
const search = reactive({ page: 1, pageSize: 20, teacher_id: undefined as number | undefined, status: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSalaryPayments(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function submit() {
  await createSalaryPayment(form)
  formVisible.value = false
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-payroll-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>薪酬发放</span>
          <el-button type="primary" @click="formVisible = true">
            标记发放
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
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
        <el-table-column prop="payment_no" label="发放单号" min-width="180" />
        <el-table-column prop="teacher_id" label="教师" width="120" />
        <el-table-column label="金额" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="payrollTagType(row.status)">
              {{ payrollStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="paid_at" label="发放时间" min-width="160" />
        <template #empty>
          <el-empty description="暂无薪酬发放记录" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="formVisible" title="标记薪酬已发放" width="460px">
      <el-form label-width="120px">
        <el-form-item label="工资条 ID">
          <el-input-number v-model="form.slip_id" :min="1" />
        </el-form-item>
        <el-form-item label="金额">
          <el-input-number v-model="form.amount_cents" :min="1" :step="1000" />
        </el-form-item>
        <el-form-item label="发放单号">
          <el-input v-model="form.payment_no" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="formVisible = false">
          取消
        </el-button>
        <el-button type="primary" @click="submit">
          保存
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

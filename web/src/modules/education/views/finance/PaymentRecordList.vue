<script setup lang="ts">
import type { PaymentRecord } from '../../api/finance/payment.ts'
import { pagePaymentRecords } from '../../api/finance/payment.ts'
import { centsToYuan, duplicateCallbackText, financePageText, financeStatusLabel, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinancePaymentRecordList' })

const loading = ref(false)
const rows = ref<PaymentRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '', channel_code: '' } as any)

async function loadRows() {
  loading.value = true
  try {
    const response = await pagePaymentRecords(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadRows()
}

function handleReset() {
  Object.assign(search, { page: 1, pageSize: 20, campus_id: undefined, status: '', channel_code: '' })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>{{ financePageText.payments.title }}</span>
          <el-button type="primary" @click="loadRows">
            {{ financePageText.payments.refresh }}
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item :label="financePageText.payments.fields.channel">
          <el-input v-model="search.channel_code" clearable />
        </el-form-item>
        <el-form-item :label="financePageText.payments.fields.status">
          <el-input v-model="search.status" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            {{ financePageText.payments.search }}
          </el-button>
          <el-button @click="handleReset">
            {{ financePageText.payments.reset }}
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="payment_no" :label="financePageText.payments.columns.paymentNo" min-width="180" />
        <el-table-column prop="channel_code" :label="financePageText.payments.columns.channel" width="130" />
        <el-table-column prop="channel_trade_no" :label="financePageText.payments.columns.tradeNo" min-width="180" />
        <el-table-column :label="financePageText.payments.columns.amount" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.payments.columns.status" width="130">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ duplicateCallbackText(row.message) || financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.payments.empty" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .search-form { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

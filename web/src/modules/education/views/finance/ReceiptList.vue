<script setup lang="ts">
import type { ReceiptRecord } from '../../api/finance/receipt.ts'
import { pageReceipts } from '../../api/finance/receipt.ts'
import { centsToYuan, financePageText, financeStatusLabel, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceReceiptList' })

const rows = ref<ReceiptRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '' })

async function loadRows() {
  const response = await pageReceipts(search)
  rows.value = response.data.list
  total.value = response.data.total
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>{{ financePageText.receipts.title }}</span>
          <el-button type="primary" @click="loadRows">
            {{ financePageText.receipts.refresh }}
          </el-button>
        </div>
      </template>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="receipt_no" :label="financePageText.receipts.columns.receiptNo" min-width="180" />
        <el-table-column prop="student_id" :label="financePageText.receipts.columns.student" width="110" />
        <el-table-column :label="financePageText.receipts.columns.amount" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.receipts.columns.status" width="130">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.receipts.empty" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

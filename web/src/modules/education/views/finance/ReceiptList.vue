<script setup lang="ts">
import type { ReceiptRecord } from '../../api/finance/receipt.ts'
import { pageReceipts, voidReceipt } from '../../api/finance/receipt.ts'
import { centsToYuan, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceReceiptList' })

const rows = ref<ReceiptRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '' })

async function loadRows() {
  const response = await pageReceipts(search)
  rows.value = response.data.list
  total.value = response.data.total
}

async function voidRow(row: ReceiptRecord) {
  await voidReceipt(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Receipts</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table :data="rows" row-key="id">
        <el-table-column prop="receipt_no" label="Receipt No" min-width="180" />
        <el-table-column prop="student_id" label="Student" width="110" />
        <el-table-column label="Amount" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="Status" width="130">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" fixed="right" width="120">
          <template #default="{ row }">
            <el-button link type="danger" @click="voidRow(row)">
              Void
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

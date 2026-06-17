<script setup lang="ts">
import type { RefundRequestRecord } from '../../api/finance/refund.ts'
import { pageRefundRequests } from '../../api/finance/refund.ts'
import RefundApprovalDrawer from './components/RefundApprovalDrawer.vue'
import { centsToYuan, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceRefundRequestList' })

const loading = ref(false)
const rows = ref<RefundRequestRecord[]>([])
const total = ref(0)
const current = ref<RefundRequestRecord | null>(null)
const drawerVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageRefundRequests(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function approve(row: RefundRequestRecord) {
  current.value = row
  drawerVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Refund Requests</span>
          <el-button type="primary" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="refund_no" label="Refund No" min-width="180" />
        <el-table-column prop="order_id" label="Order" width="110" />
        <el-table-column label="Amount" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.refund_amount_cents) }}
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
            <el-button link type="primary" @click="approve(row)">
              Approve
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <RefundApprovalDrawer v-model="drawerVisible" :row="current" @success="loadRows" />
  </div>
</template>

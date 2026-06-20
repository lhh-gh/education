<script setup lang="ts">
import type { RefundRequestRecord } from '../../api/finance/refund.ts'
import { pageRefundRequests } from '../../api/finance/refund.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import RefundApprovalDrawer from './components/RefundApprovalDrawer.vue'
import { centsToYuan, financePageText, financePermissions, financeStatusLabel, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceRefundRequestList' })

const loading = ref(false)
const rows = ref<RefundRequestRecord[]>([])
const total = ref(0)
const current = ref<RefundRequestRecord | null>(null)
const drawerVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, status: '' })
const permissions = computed(() => financePermissions(hasAuth))

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
          <span>{{ financePageText.refunds.title }}</span>
          <el-button type="primary" @click="loadRows">
            {{ financePageText.refunds.refresh }}
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="refund_no" :label="financePageText.refunds.columns.refundNo" min-width="180" />
        <el-table-column prop="order_id" :label="financePageText.refunds.columns.order" width="110" />
        <el-table-column :label="financePageText.refunds.columns.amount" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.refund_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.refunds.columns.status" width="130">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.refunds.columns.actions" fixed="right" width="120">
          <template #default="{ row }">
            <el-button v-if="permissions.refundApprove" link type="primary" @click="approve(row)">
              {{ financePageText.refunds.actions.approve }}
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.refunds.empty" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <RefundApprovalDrawer v-model="drawerVisible" :row="current" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

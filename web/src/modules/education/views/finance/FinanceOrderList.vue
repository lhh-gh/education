<script setup lang="ts">
import type { FinanceOrderRecord } from '../../api/finance/order.ts'
import { pageFinanceOrders } from '../../api/finance/order.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import OfflineCollectionForm from './components/OfflineCollectionForm.vue'
import ReceiptIssueDrawer from './components/ReceiptIssueDrawer.vue'
import { canShowOfflineCollection, centsToYuan, financeErrorMessage, financePageText, financePermissions, financeStatusLabel, financeTagType } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceOrderList' })

const loading = ref(false)
const message = useMessage()
const rows = ref<FinanceOrderRecord[]>([])
const total = ref(0)
const errorText = ref('')
const current = ref<FinanceOrderRecord | null>(null)
const offlineVisible = ref(false)
const receiptVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '', keyword: '' })
const permissions = computed(() => financePermissions(hasAuth))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageFinanceOrders(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = financeErrorMessage(error, '订单列表加载失败')
    message.error(errorText.value)
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
  Object.assign(search, { page: 1, pageSize: 20, campus_id: undefined, status: '', keyword: '' })
  loadRows()
}

function openOffline(row: FinanceOrderRecord) {
  current.value = row
  offlineVisible.value = true
}

function openReceipt(row: FinanceOrderRecord) {
  current.value = row
  receiptVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-finance-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>{{ financePageText.orders.title }}</span>
          <el-button type="primary" @click="loadRows">
            {{ financePageText.orders.refresh }}
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item :label="financePageText.orders.fields.campus">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item :label="financePageText.orders.fields.status">
          <el-select v-model="search.status" clearable style="width: 150px;">
            <el-option :label="financeStatusLabel('pending')" value="pending" />
            <el-option :label="financeStatusLabel('paid')" value="paid" />
            <el-option :label="financeStatusLabel('refunded')" value="refunded" />
          </el-select>
        </el-form-item>
        <el-form-item :label="financePageText.orders.fields.keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            {{ financePageText.orders.search }}
          </el-button>
          <el-button @click="handleReset">
            {{ financePageText.orders.reset }}
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="order_no" :label="financePageText.orders.columns.orderNo" min-width="180" />
        <el-table-column prop="student_id" :label="financePageText.orders.columns.student" width="110" />
        <el-table-column :label="financePageText.orders.columns.total" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.total_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.orders.columns.paid" width="130">
          <template #default="{ row }">
            {{ centsToYuan(row.paid_amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.orders.columns.status" width="130">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="financePageText.orders.columns.actions" fixed="right" width="220">
          <template #default="{ row }">
            <el-button v-if="canShowOfflineCollection(hasAuth)" link type="primary" @click="openOffline(row)">
              {{ financePageText.orders.actions.collect }}
            </el-button>
            <el-button v-if="permissions.receiptIssue" link @click="openReceipt(row)">
              {{ financePageText.orders.actions.receipt }}
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.orders.empty" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <OfflineCollectionForm v-model="offlineVisible" :order="current" @success="loadRows" />
    <ReceiptIssueDrawer v-model="receiptVisible" :order="current" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-alert,
  .search-form { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

<script setup lang="ts">
import type { ReconciliationBatchRecord } from '../../api/finance/reconciliation.ts'
import { pageReconciliationBatches } from '../../api/finance/reconciliation.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReconciliationImportDrawer from './components/ReconciliationImportDrawer.vue'
import { financePageText, financePermissions, financeStatusLabel, financeTagType, reconciliationRowState } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceReconciliationBatchList' })

const rows = ref<ReconciliationBatchRecord[]>([])
const total = ref(0)
const drawerVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '' })
const permissions = computed(() => financePermissions(hasAuth))

async function loadRows() {
  const response = await pageReconciliationBatches(search)
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
          <span>{{ financePageText.reconciliation.title }}</span>
          <el-button v-if="permissions.reconciliationImport" type="primary" @click="drawerVisible = true">
            {{ financePageText.reconciliation.import }}
          </el-button>
        </div>
      </template>
      <el-table :data="rows" row-key="id" :row-class-name="({ row }) => reconciliationRowState(row)">
        <el-table-column prop="batch_no" :label="financePageText.reconciliation.columns.batchNo" min-width="180" />
        <el-table-column prop="channel_code" :label="financePageText.reconciliation.columns.channel" width="130" />
        <el-table-column prop="business_date" :label="financePageText.reconciliation.columns.businessDate" width="130" />
        <el-table-column prop="total_count" :label="financePageText.reconciliation.columns.total" width="90" />
        <el-table-column prop="matched_count" :label="financePageText.reconciliation.columns.matched" width="100" />
        <el-table-column prop="unmatched_count" :label="financePageText.reconciliation.columns.unmatched" width="110" />
        <el-table-column :label="financePageText.reconciliation.columns.status" width="150">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ financeStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty :description="financePageText.reconciliation.empty" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ReconciliationImportDrawer v-model="drawerVisible" @success="loadRows" />
  </div>
</template>

<style scoped lang="scss">
.education-finance-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }

  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

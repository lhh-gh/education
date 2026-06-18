<script setup lang="ts">
import type { ReconciliationBatchRecord } from '../../api/finance/reconciliation.ts'
import { pageReconciliationBatches } from '../../api/finance/reconciliation.ts'
import ReconciliationImportDrawer from './components/ReconciliationImportDrawer.vue'
import { financeTagType, reconciliationRowState } from './financeRules.ts'

defineOptions({ name: 'EducationFinanceReconciliationBatchList' })

const rows = ref<ReconciliationBatchRecord[]>([])
const total = ref(0)
const drawerVisible = ref(false)
const search = reactive({ page: 1, pageSize: 20, campus_id: undefined as number | undefined, status: '' })

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
          <span>Reconciliation</span>
          <el-button type="primary" @click="drawerVisible = true">
            Import
          </el-button>
        </div>
      </template>
      <el-table :data="rows" row-key="id" :row-class-name="({ row }) => reconciliationRowState(row)">
        <el-table-column prop="batch_no" label="Batch No" min-width="180" />
        <el-table-column prop="channel_code" label="Channel" width="130" />
        <el-table-column prop="business_date" label="Date" width="130" />
        <el-table-column prop="total_count" label="Total" width="90" />
        <el-table-column prop="matched_count" label="Matched" width="100" />
        <el-table-column prop="unmatched_count" label="Unmatched" width="110" />
        <el-table-column label="Status" width="150">
          <template #default="{ row }">
            <el-tag :type="financeTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ReconciliationImportDrawer v-model="drawerVisible" @success="loadRows" />
  </div>
</template>

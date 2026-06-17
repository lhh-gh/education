<script setup lang="ts">
import type { AccountBalanceReportParams, AccountBalanceReportRow, AccountBalanceReportSummary } from '../../api/academic/report.ts'
import { pageAccountBalanceReport } from '../../api/academic/report.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import ReportMetricCard from './components/ReportMetricCard.vue'
import ReportStateBlock from './components/ReportStateBlock.vue'
import ReportTableToolbar from './components/ReportTableToolbar.vue'
import { canShowReportDrillLink, reportHasRows, reportTagType, summaryMetricItems } from './reportRules.ts'

defineOptions({ name: 'EducationAccountBalanceReport' })

const loading = ref(false)
const errorText = ref('')
const rows = ref<AccountBalanceReportRow[]>([])
const total = ref(0)
const summary = ref<AccountBalanceReportSummary | null>(null)
const search = reactive<AccountBalanceReportParams>({ page: 1, pageSize: 20 })
const state = computed(() => loading.value ? 'loading' : errorText.value ? 'error' : !reportHasRows(total.value, rows.value.length) ? 'empty' : null)
const summaryItems = computed(() => summaryMetricItems(summary.value ?? {}, ['account_count', 'active_count', 'total_purchased_units', 'total_consumed_units', 'total_available_units', 'low_balance_count', 'expiring_count']))
const canDrill = computed(() => hasAuth('education:academic:student-course-account:ledger'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAccountBalanceReport(search)
    rows.value = response.data.list
    total.value = response.data.total
    summary.value = response.data.summary
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Account balance report loading failed'
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
  Object.assign(search, { page: 1, pageSize: 20, tenant_id: undefined, campus_id: undefined, course_id: undefined, student_id: undefined, status: undefined, balance_level: undefined })
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-report-page pt-3">
    <el-card shadow="never">
      <template #header>Account Balance Report</template>
      <el-form :inline="true" :model="search" class="report-extra-filters">
        <el-form-item label="Tenant ID"><el-input-number v-model="search.tenant_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Campus ID"><el-input-number v-model="search.campus_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Course ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Student ID"><el-input-number v-model="search.student_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Active" value="active" />
            <el-option label="Frozen" value="frozen" />
            <el-option label="Closed" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item label="Balance">
          <el-select v-model="search.balance_level" clearable style="width: 150px;">
            <el-option label="Zero" value="zero" />
            <el-option label="Low" value="low" />
            <el-option label="Normal" value="normal" />
            <el-option label="Expired" value="expired" />
            <el-option label="Expiring Soon" value="expiring_soon" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">Search</el-button>
          <el-button @click="handleReset">Reset</el-button>
        </el-form-item>
      </el-form>
      <ReportStateBlock v-if="state" :state="state" :message="errorText" @retry="loadRows" />
      <template v-else>
        <div class="metric-grid"><ReportMetricCard v-for="item in summaryItems" :key="item.title" :title="item.title" :value="item.value" /></div>
        <ReportTableToolbar title="Account Rows" :total="total" :loading="loading" @refresh="loadRows" />
        <el-table :data="rows" row-key="account_id">
          <el-table-column prop="student_name" label="Student" min-width="130" />
          <el-table-column prop="student_no" label="Student No" width="130" />
          <el-table-column prop="course_name" label="Course" min-width="130" />
          <el-table-column prop="purchased_units" label="Purchased" width="110" />
          <el-table-column prop="bonus_units" label="Bonus" width="90" />
          <el-table-column prop="consumed_units" label="Consumed" width="110" />
          <el-table-column prop="adjusted_units" label="Adjusted" width="110" />
          <el-table-column prop="refunded_units" label="Refunded" width="110" />
          <el-table-column prop="frozen_units" label="Frozen" width="90" />
          <el-table-column prop="available_units" label="Available" width="110" />
          <el-table-column label="Status" width="100"><template #default="{ row }"><el-tag :type="reportTagType(row.status)">{{ row.status }}</el-tag></template></el-table-column>
          <el-table-column label="Balance" width="130"><template #default="{ row }"><el-tag :type="reportTagType(row.balance_level)">{{ row.balance_level }}</el-tag></template></el-table-column>
          <el-table-column prop="expires_at" label="Expires At" width="180" />
          <el-table-column label="Actions" width="110" fixed="right">
            <template #default="{ row }">
              <router-link v-if="canShowReportDrillLink(canDrill, row.account_id)" :to="`/education/academic/course-accounts?account_id=${row.account_id}`">Ledger</router-link>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
      </template>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin: 16px 0; }
</style>

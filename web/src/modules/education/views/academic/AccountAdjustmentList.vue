<script setup lang="ts">
import type { AccountAdjustmentPageParams, AccountAdjustmentRecord, RollbackResult } from '../../api/academic/attendanceConsumption.ts'
import { pageAccountAdjustments } from '../../api/academic/attendanceConsumption.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AccountAdjustmentForm from './components/AccountAdjustmentForm.vue'
import AccountAdjustmentRollbackDialog from './components/AccountAdjustmentRollbackDialog.vue'
import { canRollbackAdjustment, markAdjustmentRollbackSuccess } from './attendanceConsumptionRules.ts'

defineOptions({ name: 'EducationAcademicAccountAdjustmentList' })

const loading = ref(false)
const formVisible = ref(false)
const rollbackVisible = ref(false)
const current = ref<AccountAdjustmentRecord | null>(null)
const errorText = ref('')
const rows = ref<AccountAdjustmentRecord[]>([])
const total = ref(0)
const search = reactive<AccountAdjustmentPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:account-adjustment:create'))
const canRollback = computed(() => hasAuth('education:academic:account-adjustment:rollback'))

function defaultSearch(): AccountAdjustmentPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, account_id: undefined, student_id: undefined, course_id: undefined, adjustment_type: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAccountAdjustments(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Account adjustment loading failed'
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
  Object.assign(search, defaultSearch())
  loadRows()
}

function openRollback(row: AccountAdjustmentRecord) {
  current.value = row
  rollbackVisible.value = true
}

function onCreated(row: AccountAdjustmentRecord) {
  rows.value = [row, ...rows.value]
  loadRows()
}

function onRollback(result: RollbackResult<AccountAdjustmentRecord>) {
  rows.value = markAdjustmentRollbackSuccess(rows.value, result.original)
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Account Adjustments</span>
          <el-button v-if="canCreate" type="primary" @click="formVisible = true">Supplement Deduction</el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID"><el-input-number v-model="search.tenant_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Campus ID"><el-input-number v-model="search.campus_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Account ID"><el-input-number v-model="search.account_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Student ID"><el-input-number v-model="search.student_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Course ID"><el-input-number v-model="search.course_id" :min="1" :controls="false" /></el-form-item>
        <el-form-item label="Type">
          <el-select v-model="search.adjustment_type" clearable style="width: 180px;"><el-option label="Supplement Deduction" value="supplement_deduction" /><el-option label="Rollback" value="rollback" /></el-select>
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;"><el-option label="Confirmed" value="confirmed" /><el-option label="Rolled Back" value="rolled_back" /></el-select>
        </el-form-item>
        <el-form-item label="Keyword"><el-input v-model="search.keyword" clearable /></el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">Search</el-button>
          <el-button @click="handleReset">Reset</el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="adjustment_no" label="No" width="180" />
        <el-table-column prop="student_name" label="Student" min-width="130" />
        <el-table-column prop="course_name" label="Course" min-width="140" />
        <el-table-column prop="adjustment_type" label="Type" width="170" />
        <el-table-column prop="direction" label="Direction" width="110" />
        <el-table-column prop="units" label="Units" width="90" />
        <el-table-column prop="before_available_units" label="Before" width="110" />
        <el-table-column prop="after_available_units" label="After" width="110" />
        <el-table-column prop="status" label="Status" width="120" />
        <el-table-column prop="reason" label="Reason" min-width="180" />
        <el-table-column prop="created_at" label="Created" width="170" />
        <el-table-column label="Actions" fixed="right" width="140">
          <template #default="{ row }">
            <el-button v-if="canRollbackAdjustment(row, canRollback)" link type="warning" @click="openRollback(row)">Rollback</el-button>
          </template>
        </el-table-column>
        <template #empty><el-empty description="No account adjustments" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <AccountAdjustmentForm v-model="formVisible" :tenant-id="search.tenant_id" @success="onCreated" />
    <AccountAdjustmentRollbackDialog v-model="rollbackVisible" :row="current" :tenant-id="search.tenant_id" @success="onRollback" />
  </div>
</template>

<style scoped lang="scss">
.education-academic-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

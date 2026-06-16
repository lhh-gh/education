<script setup lang="ts">
import type { StudentCourseAccountPageParams, StudentCourseAccountRecord, StudentCourseAccountStatus } from '../../api/academic/courseAccount.ts'
import { changeStudentCourseAccountStatus, pageStudentCourseAccounts } from '../../api/academic/courseAccount.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AccountLedgerDrawer from './components/AccountLedgerDrawer.vue'
import { accountStatusAction } from './courseAccountRules.ts'

defineOptions({ name: 'EducationAcademicAccountLedgerList' })

const message = useMessage()
const loading = ref(false)
const drawerVisible = ref(false)
const current = ref<StudentCourseAccountRecord | null>(null)
const errorText = ref('')
const rows = ref<StudentCourseAccountRecord[]>([])
const total = ref(0)
const search = reactive<StudentCourseAccountPageParams>(defaultSearch())

const canLedger = computed(() => hasAuth('education:academic:student-course-account:ledger'))
const canStatus = computed(() => hasAuth('education:academic:student-course-account:status'))

function defaultSearch(): StudentCourseAccountPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, student_id: undefined, course_id: undefined, status: undefined, keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageStudentCourseAccounts(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Student course account list loading failed'
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

function openLedger(row: StudentCourseAccountRecord) {
  current.value = row
  drawerVisible.value = true
}

async function changeStatus(row: StudentCourseAccountRecord, status: StudentCourseAccountStatus) {
  await changeStudentCourseAccountStatus(row.id, status, search.tenant_id)
  await loadRows()
}

async function toggleFreeze(row: StudentCourseAccountRecord) {
  const action = accountStatusAction(row.status)
  if (action === 'closed') {
    return
  }
  await changeStatus(row, action === 'freeze' ? 'frozen' : 'active')
}

async function closeAccount(row: StudentCourseAccountRecord) {
  await message.confirm('Close this account?')
  await changeStatus(row, 'closed')
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Course Accounts</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Campus ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Student ID">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Course ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Active" value="active" />
            <el-option label="Frozen" value="frozen" />
            <el-option label="Closed" value="closed" />
          </el-select>
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            Search
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="student_no" label="Student No" width="140" />
        <el-table-column prop="student_name" label="Student" min-width="140" />
        <el-table-column prop="course_name" label="Course" min-width="160" />
        <el-table-column prop="purchased_units" label="Purchased" width="110" />
        <el-table-column prop="bonus_units" label="Bonus" width="100" />
        <el-table-column prop="consumed_units" label="Consumed" width="110" />
        <el-table-column prop="refunded_units" label="Refunded" width="110" />
        <el-table-column prop="frozen_units" label="Frozen Units" width="120" />
        <el-table-column prop="available_units" label="Available" width="110" />
        <el-table-column prop="status" label="Status" width="100" />
        <el-table-column prop="expires_at" label="Expires" width="140" />
        <el-table-column label="Actions" fixed="right" width="260">
          <template #default="{ row }">
            <el-button v-if="canLedger" link type="primary" @click="openLedger(row)">
              Ledger
            </el-button>
            <el-button v-if="canStatus && row.status !== 'closed'" link type="primary" @click="toggleFreeze(row)">
              {{ accountStatusAction(row.status) === 'freeze' ? 'Freeze' : 'Unfreeze' }}
            </el-button>
            <el-button v-if="canStatus && row.status !== 'closed' && Number(row.available_units) === 0" link type="danger" @click="closeAccount(row)">
              Close
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No course accounts" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <AccountLedgerDrawer v-model="drawerVisible" :account-id="current?.id" :tenant-id="search.tenant_id" />
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

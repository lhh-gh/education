<script setup lang="ts">
import type { EnrollmentCreateResult, EnrollmentPageParams, EnrollmentRecord } from '../../api/academic/courseAccount.ts'
import { cancelEnrollment, pageEnrollments } from '../../api/academic/courseAccount.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import EnrollmentCreateDrawer from './components/EnrollmentCreateDrawer.vue'
import { enrollmentSuccessSummary } from './courseAccountRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAcademicEnrollmentWorkbench' })

const message = useMessage()
const loading = ref(false)
const drawerVisible = ref(false)
const errorText = ref('')
const successText = ref('')
const rows = ref<EnrollmentRecord[]>([])
const total = ref(0)
const search = reactive<EnrollmentPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:enrollment:create'))
const canCancel = computed(() => hasAuth('education:academic:enrollment:cancel'))

function defaultSearch(): EnrollmentPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, student_id: undefined, course_id: undefined, status: undefined, keyword: '', enrolled_at_start: '', enrolled_at_end: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageEnrollments(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Enrollment list loading failed'
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

function openCreate() {
  drawerVisible.value = true
}

function onEnrollmentCreated(result: EnrollmentCreateResult) {
  successText.value = enrollmentSuccessSummary(result)
  loadRows()
}

async function cancelRow(row: EnrollmentRecord) {
  const result = await message.prompt('Cancel reason', '', 'Cancel Enrollment', value => Boolean(value?.trim()) || 'Reason is required') as { value: string }
  try {
    await cancelEnrollment(row.id, result.value.trim(), search.tenant_id)
    message.success('Enrollment cancelled')
    await loadRows()
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Enrollment cancel failed'
    message.error(errorText.value)
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Enrollments</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            New
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="false" :title="successText" />
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
          <el-select v-model="search.status" clearable style="width: 140px;">
            <el-option label="Pending" value="pending" />
            <el-option label="Confirmed" value="confirmed" />
            <el-option label="Cancelled" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="Start">
          <el-date-picker v-model="search.enrolled_at_start" type="date" value-format="YYYY-MM-DD" />
        </el-form-item>
        <el-form-item label="End">
          <el-date-picker v-model="search.enrolled_at_end" type="date" value-format="YYYY-MM-DD" />
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
        <el-table-column prop="enrollment_no" label="Enrollment No" width="210" />
        <el-table-column prop="student_name_snapshot" label="Student" min-width="140" />
        <el-table-column prop="course_name_snapshot" label="Course" min-width="140" />
        <el-table-column prop="package_name_snapshot" label="Package" min-width="150" />
        <el-table-column prop="total_units" label="Units" width="100" />
        <el-table-column prop="deal_amount" label="Deal" width="120" />
        <el-table-column prop="status" label="Status" width="110" />
        <el-table-column prop="enrolled_at" label="Enrolled" width="180" />
        <el-table-column prop="confirmed_at" label="Confirmed" width="180" />
        <el-table-column label="Actions" fixed="right" width="120">
          <template #default="{ row }">
            <el-button v-if="canCancel && row.status !== 'cancelled'" link type="danger" @click="cancelRow(row)">
              Cancel
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No enrollments" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <EnrollmentCreateDrawer v-model="drawerVisible" :tenant-id="search.tenant_id" :campus-id="search.campus_id" @success="onEnrollmentCreated" />
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

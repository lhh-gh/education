<script setup lang="ts">
import type { AttendanceLessonPageParams, AttendanceLessonRecord, AttendanceSubmitResult } from '../../api/academic/attendanceConsumption.ts'
import { pageAttendanceLessons } from '../../api/academic/attendanceConsumption.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AttendanceResultDrawer from './components/AttendanceResultDrawer.vue'
import AttendanceSubmitDrawer from './components/AttendanceSubmitDrawer.vue'
import { canSubmitAttendance } from './attendanceConsumptionRules.ts'

defineOptions({ name: 'EducationAcademicAttendanceReview' })

const loading = ref(false)
const submitVisible = ref(false)
const resultVisible = ref(false)
const current = ref<AttendanceLessonRecord | null>(null)
const result = ref<AttendanceSubmitResult | null>(null)
const errorText = ref('')
const rows = ref<AttendanceLessonRecord[]>([])
const total = ref(0)
const search = reactive<AttendanceLessonPageParams>(defaultSearch())

const canDetail = computed(() => hasAuth('education:academic:attendance:detail'))
const canSubmit = computed(() => hasAuth('education:academic:attendance:submit'))

function defaultSearch(): AttendanceLessonPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, class_id: undefined, teacher_id: undefined, status: undefined, start_at: '', end_at: '', keyword: '' }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAttendanceLessons(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Attendance lesson list loading failed'
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

function openSubmit(row: AttendanceLessonRecord) {
  current.value = row
  submitVisible.value = true
}

function onSubmitted(payload: AttendanceSubmitResult) {
  result.value = payload
  resultVisible.value = true
  rows.value = rows.value.map(row => row.id === current.value?.id ? { ...row, status: 'completed' } : row)
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Attendance Review</span>
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
        <el-form-item label="Class ID">
          <el-input-number v-model="search.class_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Teacher ID">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Scheduled" value="scheduled" />
            <el-option label="Completed" value="completed" />
            <el-option label="Cancelled" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item label="Range">
          <el-date-picker v-model="search.start_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="Start" />
          <el-date-picker v-model="search.end_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="End" class="ml-2" />
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
        <el-table-column prop="lesson_no" label="Lesson No" width="160" />
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column prop="class_name_snapshot" label="Class" min-width="150" />
        <el-table-column prop="teacher_name_snapshot" label="Teacher" min-width="140" />
        <el-table-column prop="start_at" label="Start" width="180" />
        <el-table-column prop="end_at" label="End" width="180" />
        <el-table-column prop="student_count" label="Students" width="100" />
        <el-table-column prop="status" label="Status" width="110" />
        <el-table-column label="Actions" fixed="right" width="160">
          <template #default="{ row }">
            <el-button v-if="canDetail && canSubmitAttendance(row.status, canSubmit)" link type="primary" @click="openSubmit(row)">
              Submit
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No lessons in selected range" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <AttendanceSubmitDrawer v-model="submitVisible" :lesson-id="current?.id" :tenant-id="search.tenant_id" @submitted="onSubmitted" />
    <AttendanceResultDrawer v-model="resultVisible" :result="result" />
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

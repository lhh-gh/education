<script setup lang="ts">
import type { AttendanceLessonPageParams, AttendanceLessonRecord, AttendanceSubmitResult } from '../../api/academic/attendanceConsumption.ts'
import { pageAttendanceLessons } from '../../api/academic/attendanceConsumption.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import AttendanceResultDrawer from './components/AttendanceResultDrawer.vue'
import AttendanceSubmitDrawer from './components/AttendanceSubmitDrawer.vue'
import { canSubmitAttendance } from './attendanceConsumptionRules.ts'
import { lessonStatusLabel, lessonStatusTagType } from './classScheduleRules.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicAttendanceReview' })

const { scope } = useEducationScope()

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
  return { page: 1, page_size: 20, class_id: undefined, teacher_id: undefined, status: undefined, start_at: '', end_at: '', keyword: '' }
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
    errorText.value = error?.message ?? '考勤课次列表加载失败'
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
          <span>考勤复核</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="班级ID">
          <el-input-number v-model="search.class_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="教师ID">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="待上课" value="scheduled" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item label="时间范围">
          <el-date-picker v-model="search.start_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="开始时间" />
          <el-date-picker v-model="search.end_at" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" placeholder="结束时间" class="ml-2" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            重置
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="lesson_no" label="课次编号" width="160" />
        <el-table-column prop="title" label="课次标题" min-width="180" />
        <el-table-column prop="class_name_snapshot" label="班级" min-width="150" />
        <el-table-column prop="teacher_name_snapshot" label="教师" min-width="140" />
        <el-table-column prop="start_at" label="开始时间" width="180" />
        <el-table-column prop="end_at" label="结束时间" width="180" />
        <el-table-column prop="student_count" label="学员数" width="100" />
        <el-table-column label="状态" width="110">
          <template #default="{ row }">
            <el-tag :type="lessonStatusTagType(row.status)">
              {{ lessonStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="160">
          <template #default="{ row }">
            <el-button v-if="canDetail && canSubmitAttendance(row.status, canSubmit)" link type="primary" @click="openSubmit(row)">
              提交考勤
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="当前范围暂无课次" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <AttendanceSubmitDrawer v-model="submitVisible" :lesson-id="current?.id" :tenant-id="scope.tenant_id" @submitted="onSubmitted" />
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

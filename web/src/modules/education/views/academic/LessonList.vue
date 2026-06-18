<script setup lang="ts">
import type { LessonPageParams, LessonRecord } from '../../api/academic/classSchedule.ts'
import { cancelLesson, deleteLesson, pageLessons } from '../../api/academic/classSchedule.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonDetailDrawer from './components/LessonDetailDrawer.vue'
import LessonForm from './components/LessonForm.vue'

defineOptions({ name: 'EducationAcademicLessonList' })

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const detailVisible = ref(false)
const current = ref<LessonRecord | null>(null)
const errorText = ref('')
const rows = ref<LessonRecord[]>([])
const total = ref(0)
const search = reactive<LessonPageParams>(defaultSearch())

const canDetail = computed(() => hasAuth('education:academic:lesson:detail'))
const canEdit = computed(() => hasAuth('education:academic:lesson:update'))
const canCancel = computed(() => hasAuth('education:academic:lesson:cancel'))
const canDelete = computed(() => hasAuth('education:academic:lesson:delete'))

function defaultSearch(): LessonPageParams {
  return {
    page: 1,
    page_size: 20,
    tenant_id: undefined,
    campus_id: undefined,
    class_id: undefined,
    course_id: undefined,
    teacher_id: undefined,
    classroom_id: undefined,
    status: undefined,
    start_at: '',
    end_at: '',
    keyword: '',
  }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLessons(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Lesson list loading failed'
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

function openDetail(row: LessonRecord) {
  current.value = row
  detailVisible.value = true
}

function openEdit(row: LessonRecord) {
  current.value = row
  dialogVisible.value = true
}

async function cancelRow(row: LessonRecord) {
  const cancelReason = await message.prompt('Cancel reason', 'Cancel Lesson')
  const reason = typeof cancelReason === 'string' ? cancelReason : (cancelReason as any)?.value
  const response = await cancelLesson(row.id, reason || 'Cancelled by admin', search.tenant_id)
  rows.value = rows.value.map(item => item.id === row.id ? { ...item, ...response.data } : item)
}

async function removeRow(row: LessonRecord) {
  try {
    await message.confirm('Delete this lesson?')
    await deleteLesson(row.id, search.tenant_id)
    rows.value = rows.value.filter(item => item.id !== row.id)
  }
  catch (error: any) {
    if (error?.message) {
      errorText.value = error.message
      message.error(errorText.value)
    }
  }
}

function onLessonUpdated(lesson: LessonRecord) {
  dialogVisible.value = false
  rows.value = rows.value.map(item => item.id === lesson.id ? { ...item, ...lesson } : item)
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Lessons</span>
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
        <el-form-item label="Course ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Teacher ID">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Classroom ID">
          <el-input-number v-model="search.classroom_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Scheduled" value="scheduled" />
            <el-option label="Cancelled" value="cancelled" />
            <el-option label="Completed" value="completed" />
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
        <el-table-column prop="class_name_snapshot" label="Class" min-width="140" />
        <el-table-column prop="course_name_snapshot" label="Course" min-width="140" />
        <el-table-column prop="teacher_name_snapshot" label="Teacher" min-width="130" />
        <el-table-column prop="classroom_name_snapshot" label="Classroom" min-width="130" />
        <el-table-column prop="start_at" label="Start" width="180" />
        <el-table-column prop="end_at" label="End" width="180" />
        <el-table-column prop="lesson_units" label="Units" width="90" />
        <el-table-column prop="student_count" label="Students" width="100" />
        <el-table-column prop="status" label="Status" width="100" />
        <el-table-column prop="source_type" label="Source" width="100" />
        <el-table-column label="Actions" fixed="right" width="250">
          <template #default="{ row }">
            <el-button v-if="canDetail" link type="primary" @click="openDetail(row)">
              Detail
            </el-button>
            <el-button v-if="canEdit && row.status === 'scheduled'" link type="primary" @click="openEdit(row)">
              Edit
            </el-button>
            <el-button v-if="canCancel && row.status === 'scheduled'" link type="warning" @click="cancelRow(row)">
              Cancel
            </el-button>
            <el-button v-if="canDelete" link type="danger" @click="removeRow(row)">
              Delete
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No lessons" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" title="Edit Lesson" width="680px">
      <LessonForm :tenant-id="search.tenant_id" :data="current" @success="onLessonUpdated" />
    </el-dialog>
    <LessonDetailDrawer v-model="detailVisible" :lesson-id="current?.id" :tenant-id="search.tenant_id" />
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

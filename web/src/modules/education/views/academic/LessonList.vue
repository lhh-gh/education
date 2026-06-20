<script setup lang="ts">
import type { LessonPageParams, LessonRecord } from '../../api/academic/classSchedule.ts'
import { cancelLesson, deleteLesson, pageLessons } from '../../api/academic/classSchedule.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonDetailDrawer from './components/LessonDetailDrawer.vue'
import LessonForm from './components/LessonForm.vue'
import { lessonStatusLabel, lessonStatusTagType } from './classScheduleRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicLessonList' })

const { scope } = useEducationScope()

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
    errorText.value = error?.message ?? '课次列表加载失败'
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
  const cancelReason = await message.prompt('取消原因', '取消课次')
  const reason = typeof cancelReason === 'string' ? cancelReason : (cancelReason as any)?.value
  const response = await cancelLesson(row.id, reason || '后台取消', scope.tenant_id)
  rows.value = rows.value.map(item => item.id === row.id ? { ...item, ...response.data } : item)
}

async function removeRow(row: LessonRecord) {
  try {
    await message.confirm('确认删除该课次？')
    await deleteLesson(row.id, scope.tenant_id)
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
          <span>课次管理</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="班级ID">
          <el-input-number v-model="search.class_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="课程ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="教师ID">
          <el-input-number v-model="search.teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="教室ID">
          <el-input-number v-model="search.classroom_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="待上课" value="scheduled" />
            <el-option label="已取消" value="cancelled" />
            <el-option label="已完成" value="completed" />
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
        <el-table-column prop="class_name_snapshot" label="班级" min-width="140" />
        <el-table-column prop="course_name_snapshot" label="课程" min-width="140" />
        <el-table-column prop="teacher_name_snapshot" label="教师" min-width="130" />
        <el-table-column prop="classroom_name_snapshot" label="教室" min-width="130" />
        <el-table-column prop="start_at" label="开始时间" width="180" />
        <el-table-column prop="end_at" label="结束时间" width="180" />
        <el-table-column prop="lesson_units" label="课时" width="90" />
        <el-table-column prop="student_count" label="学员数" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="lessonStatusTagType(row.status)">
              {{ lessonStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="source_type" label="来源" width="100" />
        <el-table-column label="操作" fixed="right" width="250">
          <template #default="{ row }">
            <el-button v-if="canDetail" link type="primary" @click="openDetail(row)">
              详情
            </el-button>
            <el-button v-if="canEdit && row.status === 'scheduled'" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="canCancel && row.status === 'scheduled'" link type="warning" @click="cancelRow(row)">
              取消
            </el-button>
            <el-button v-if="canDelete" link type="danger" @click="removeRow(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无课次" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" title="编辑课次" width="680px">
      <LessonForm :tenant-id="scope.tenant_id" :data="current" @success="onLessonUpdated" />
    </el-dialog>
    <LessonDetailDrawer v-model="detailVisible" :lesson-id="current?.id" :tenant-id="scope.tenant_id" />
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

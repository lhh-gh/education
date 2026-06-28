<script setup lang="ts">
import type { ClassPageParams, ClassRecord } from '../../api/academic/classSchedule.ts'
import { changeClassStatus, deleteClass, pageClasses } from '../../api/academic/classSchedule.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { academicActionText, academicStatusLabel, academicStatusTagType } from './actionRules.ts'
import ClassForm from './components/ClassForm.vue'
import ClassStudentDrawer from './components/ClassStudentDrawer.vue'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicClassList' })

const { scope } = useEducationScope()

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const drawerVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<ClassRecord | null>(null)
const errorText = ref('')
const rows = ref<ClassRecord[]>([])
const total = ref(0)
const search = reactive<ClassPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:class:create'))
const canEdit = computed(() => hasAuth('education:academic:class:update'))
const canStatus = computed(() => hasAuth('education:academic:class:status'))
const canDelete = computed(() => hasAuth('education:academic:class:delete'))
const canStudentPage = computed(() => hasAuth('education:academic:class-student:page'))
const canStudentSave = computed(() => hasAuth('education:academic:class-student:save'))

function defaultSearch(): ClassPageParams {
  return { page: 1, page_size: 20, course_id: undefined, main_teacher_id: undefined, keyword: '', status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageClasses(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '班级列表加载失败'
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
  dialogMode.value = 'create'
  current.value = null
  dialogVisible.value = true
}

function openEdit(row: ClassRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

function openStudents(row: ClassRecord) {
  current.value = row
  drawerVisible.value = true
}

async function changeStatus(row: ClassRecord) {
  await changeClassStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', scope.tenant_id)
  await loadRows()
}

async function removeRow(row: ClassRecord) {
  await message.confirm('确认删除该班级？')
  await deleteClass(row.id, scope.tenant_id)
  await loadRows()
}

function onFormSuccess() {
  dialogVisible.value = false
  search.page = 1
  loadRows()
}

function onStudentsSuccess() {
  drawerVisible.value = false
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>班级管理</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新增
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="课程ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="教师ID">
          <el-input-number v-model="search.main_teacher_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="启用" value="enabled" />
            <el-option label="停用" value="disabled" />
          </el-select>
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
        <el-table-column prop="code" label="班级编码" width="130" />
        <el-table-column prop="name" label="班级名称" min-width="160" />
        <el-table-column prop="course_name" label="课程" min-width="160" />
        <el-table-column prop="main_teacher_name" label="主讲教师" min-width="140" />
        <el-table-column prop="classroom_name" label="教室" min-width="140" />
        <el-table-column prop="class_type" label="班型" width="110" />
        <el-table-column prop="max_students" label="容量" width="80" />
        <el-table-column prop="active_student_count" label="在班人数" width="90" />
        <el-table-column prop="lesson_units" label="课时" width="90" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="academicStatusTagType(row.status)">
              {{ academicStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="300">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="canStudentPage || canStudentSave" link type="primary" @click="openStudents(row)">
              学员
            </el-button>
            <el-button v-if="canStatus" link type="primary" @click="changeStatus(row)">
              {{ academicActionText(row.status) }}
            </el-button>
            <el-button v-if="canDelete" link type="danger" @click="removeRow(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无班级" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新增班级' : '编辑班级'" width="700px">
      <ClassForm :mode="dialogMode" :tenant-id="scope.tenant_id" :campus-id="scope.campus_id" :data="current" @success="onFormSuccess" />
    </el-dialog>
    <ClassStudentDrawer v-model="drawerVisible" :class-id="current?.id" :tenant-id="scope.tenant_id" :campus-id="current?.campus_id ?? scope.campus_id" :readonly="!canStudentSave" @success="onStudentsSuccess" />
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

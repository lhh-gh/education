<script setup lang="ts">
import type { CoursePageParams, CourseRecord } from '../../api/academic/courseAccount.ts'
import { changeCourseStatus, deleteCourse, pageCourses } from '../../api/academic/courseAccount.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { academicActionText, academicStatusLabel, academicStatusTagType } from './actionRules.ts'
import CourseForm from './components/CourseForm.vue'
import CourseTeacherDrawer from './components/CourseTeacherDrawer.vue'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicCourseList' })

const { scope } = useEducationScope()

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const drawerVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<CourseRecord | null>(null)
const errorText = ref('')
const rows = ref<CourseRecord[]>([])
const total = ref(0)
const search = reactive<CoursePageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:course:create'))
const canEdit = computed(() => hasAuth('education:academic:course:update'))
const canStatus = computed(() => hasAuth('education:academic:course:status'))
const canDelete = computed(() => hasAuth('education:academic:course:delete'))
const canTeacherPage = computed(() => hasAuth('education:academic:course-teacher:page'))
const canTeacherSave = computed(() => hasAuth('education:academic:course-teacher:save'))

function defaultSearch(): CoursePageParams {
  return { page: 1, page_size: 20, keyword: '', status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageCourses(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '课程列表加载失败'
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

function openEdit(row: CourseRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

function openTeachers(row: CourseRecord) {
  current.value = row
  drawerVisible.value = true
}

async function changeStatus(row: CourseRecord) {
  await changeCourseStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', scope.tenant_id)
  await loadRows()
}

async function removeRow(row: CourseRecord) {
  await message.confirm('确认删除该课程？')
  await deleteCourse(row.id, scope.tenant_id)
  await loadRows()
}

function onFormSuccess() {
  dialogVisible.value = false
  loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-academic-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>课程管理</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新增
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
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
        <el-table-column prop="code" label="课程编码" width="130" />
        <el-table-column prop="name" label="课程名称" min-width="160" />
        <el-table-column prop="campus_id" label="校区" width="100" />
        <el-table-column prop="category" label="课程分类" width="120" />
        <el-table-column prop="subject" label="科目" width="120" />
        <el-table-column prop="unit_minutes" label="课时分钟" width="100" />
        <el-table-column prop="teacher_count" label="教师数" width="100" />
        <el-table-column prop="package_count" label="课包数" width="110" />
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
            <el-button v-if="canTeacherPage || canTeacherSave" link type="primary" @click="openTeachers(row)">
              教师
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
          <el-empty description="暂无课程" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新增课程' : '编辑课程'" width="640px">
      <CourseForm :mode="dialogMode" :tenant-id="scope.tenant_id" :data="current" @success="onFormSuccess" />
    </el-dialog>
    <CourseTeacherDrawer v-model="drawerVisible" :course-id="current?.id" :tenant-id="scope.tenant_id" :campus-id="current?.campus_id ?? scope.campus_id" :readonly="!canTeacherSave" @success="loadRows" />
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

<script setup lang="ts">
import type { StudentPageParams, StudentRecord } from '../../api/academic/profile.ts'
import { deleteStudent, pageStudents, updateStudentStatus } from '../../api/academic/profile.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import StudentForm from './components/StudentForm.vue'
import StudentGuardianDrawer from './components/StudentGuardianDrawer.vue'
import { academicActionText, academicGenderLabel, academicStatusLabel, academicStatusTagType } from './actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicStudentList' })

const { scope } = useEducationScope()

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const drawerVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<StudentRecord | null>(null)
const errorText = ref('')
const rows = ref<StudentRecord[]>([])
const total = ref(0)
const search = reactive<StudentPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:student:create'))
const canEdit = computed(() => hasAuth('education:academic:student:update'))
const canStatus = computed(() => hasAuth('education:academic:student:status'))
const canDelete = computed(() => hasAuth('education:academic:student:delete'))
const canGuardians = computed(() => hasAuth('education:academic:student-guardian:save'))

function defaultSearch(): StudentPageParams {
  return { page: 1, page_size: 20, keyword: '', gender: undefined, status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageStudents(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '学员列表加载失败'
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

function openEdit(row: StudentRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

function openGuardians(row: StudentRecord) {
  current.value = row
  drawerVisible.value = true
}

async function changeStatus(row: StudentRecord) {
  await updateStudentStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', scope.tenant_id)
  await loadRows()
}

async function removeRow(row: StudentRecord) {
  await message.confirm('确认删除该学员？')
  await deleteStudent(row.id, scope.tenant_id)
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
          <span>学员管理</span>
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
        <el-form-item label="性别">
          <el-select v-model="search.gender" clearable style="width: 130px;">
            <el-option label="男" value="male" />
            <el-option label="女" value="female" />
            <el-option label="未知" value="unknown" />
          </el-select>
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
        <el-table-column prop="student_no" label="学员编号" width="140" />
        <el-table-column prop="name" label="姓名" min-width="150" />
        <el-table-column label="性别" width="100">
          <template #default="{ row }">
            {{ academicGenderLabel(row.gender) }}
          </template>
        </el-table-column>
        <el-table-column prop="campus_id" label="校区" width="100" />
        <el-table-column prop="mobile" label="手机号" width="140" />
        <el-table-column prop="school" label="学校" min-width="150" />
        <el-table-column prop="grade" label="年级" width="120" />
        <el-table-column prop="guardian_count" label="监护人" width="110" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="academicStatusTagType(row.status)">
              {{ academicStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="280">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="canGuardians" link type="primary" @click="openGuardians(row)">
              监护人
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
          <el-empty description="暂无学员" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新增学员' : '编辑学员'" width="600px">
      <StudentForm :mode="dialogMode" :tenant-id="scope.tenant_id" :data="current" @success="onFormSuccess" />
    </el-dialog>
    <StudentGuardianDrawer v-model="drawerVisible" :student-id="current?.id" :tenant-id="scope.tenant_id" @success="loadRows" />
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

<script setup lang="ts">
import type { TeacherPageParams, TeacherRecord } from '../../api/academic/profile.ts'
import { deleteTeacher, pageTeachers, updateTeacherStatus } from '../../api/academic/profile.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import TeacherForm from './components/TeacherForm.vue'
import { academicActionText, academicStatusLabel, academicStatusTagType } from './actionRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAcademicTeacherList' })

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<TeacherRecord | null>(null)
const errorText = ref('')
const rows = ref<TeacherRecord[]>([])
const total = ref(0)
const search = reactive<TeacherPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:teacher:create'))
const canEdit = computed(() => hasAuth('education:academic:teacher:update'))
const canStatus = computed(() => hasAuth('education:academic:teacher:status'))
const canDelete = computed(() => hasAuth('education:academic:teacher:delete'))

function defaultSearch(): TeacherPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, keyword: '', gender: undefined, status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageTeachers(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '教师列表加载失败'
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

function openEdit(row: TeacherRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

async function changeStatus(row: TeacherRecord) {
  await updateTeacherStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', search.tenant_id)
  await loadRows()
}

async function removeRow(row: TeacherRecord) {
  await message.confirm('确认删除该教师？')
  await deleteTeacher(row.id, search.tenant_id)
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
          <span>教师管理</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新增
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="机构ID">
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="校区ID">
          <el-input-number v-model="search.campus_id" :min="1" :controls="false" />
        </el-form-item>
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
        <el-table-column prop="teacher_no" label="教师编号" width="140" />
        <el-table-column prop="name" label="姓名" min-width="150" />
        <el-table-column prop="mobile" label="手机号" width="140" />
        <el-table-column prop="campus_id" label="校区" width="100" />
        <el-table-column prop="user_profile_id" label="用户档案" width="100" />
        <el-table-column prop="title" label="职称" min-width="140" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="academicStatusTagType(row.status)">
              {{ academicStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="220">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              编辑
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
          <el-empty description="暂无教师" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新增教师' : '编辑教师'" width="600px">
      <TeacherForm :mode="dialogMode" :tenant-id="search.tenant_id" :data="current" @success="onFormSuccess" />
    </el-dialog>
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

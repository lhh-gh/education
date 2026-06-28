<script setup lang="ts">
import type { LessonPackagePageParams, LessonPackageRecord } from '../../api/academic/courseAccount.ts'
import { changeLessonPackageStatus, deleteLessonPackage, pageLessonPackages } from '../../api/academic/courseAccount.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { academicActionText, academicStatusLabel, academicStatusTagType } from './actionRules.ts'
import LessonPackageForm from './components/LessonPackageForm.vue'
import { computePackageTotal } from './courseAccountRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { useEducationScope } from '@/composables/education/useEducationScope.ts'

defineOptions({ name: 'EducationAcademicLessonPackageList' })

const { scope } = useEducationScope()

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<LessonPackageRecord | null>(null)
const errorText = ref('')
const rows = ref<LessonPackageRecord[]>([])
const total = ref(0)
const search = reactive<LessonPackagePageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:lesson-package:create'))
const canEdit = computed(() => hasAuth('education:academic:lesson-package:update'))
const canStatus = computed(() => hasAuth('education:academic:lesson-package:status'))
const canDelete = computed(() => hasAuth('education:academic:lesson-package:delete'))

function defaultSearch(): LessonPackagePageParams {
  return { page: 1, page_size: 20, course_id: undefined, keyword: '', status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLessonPackages(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? '课包列表加载失败'
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

function openEdit(row: LessonPackageRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

async function changeStatus(row: LessonPackageRecord) {
  await changeLessonPackageStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', scope.tenant_id)
  await loadRows()
}

async function removeRow(row: LessonPackageRecord) {
  await message.confirm('确认删除该课包？')
  await deleteLessonPackage(row.id, scope.tenant_id)
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
          <span>课包管理</span>
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
        <el-table-column prop="code" label="课包编码" width="130" />
        <el-table-column prop="name" label="课包名称" min-width="160" />
        <el-table-column prop="course_name" label="课程" min-width="160" />
        <el-table-column prop="lesson_units" label="课时" width="100" />
        <el-table-column prop="bonus_units" label="赠送课时" width="100" />
        <el-table-column label="总课时" width="100">
          <template #default="{ row }">
            {{ row.total_units || computePackageTotal(row.lesson_units, row.bonus_units) }}
          </template>
        </el-table-column>
        <el-table-column prop="sale_price" label="售价" width="120" />
        <el-table-column prop="validity_days" label="有效期(天)" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="academicStatusTagType(row.status)">
              {{ academicStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" />
        <el-table-column label="操作" fixed="right" width="240">
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
          <el-empty description="暂无课包" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新增课包' : '编辑课包'" width="640px">
      <LessonPackageForm :mode="dialogMode" :tenant-id="scope.tenant_id" :campus-id="scope.campus_id" :course-id="search.course_id" :data="current" @success="onFormSuccess" />
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

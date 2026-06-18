<script setup lang="ts">
import type { LessonPackagePageParams, LessonPackageRecord } from '../../api/academic/courseAccount.ts'
import { changeLessonPackageStatus, deleteLessonPackage, pageLessonPackages } from '../../api/academic/courseAccount.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import LessonPackageForm from './components/LessonPackageForm.vue'
import { computePackageTotal } from './courseAccountRules.ts'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAcademicLessonPackageList' })

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
  return { page: 1, page_size: 20, tenant_id: undefined, campus_id: undefined, course_id: undefined, keyword: '', status: undefined }
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
    errorText.value = error?.message ?? 'Lesson package list loading failed'
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
  await changeLessonPackageStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', search.tenant_id)
  await loadRows()
}

async function removeRow(row: LessonPackageRecord) {
  await message.confirm('Delete this lesson package?')
  await deleteLessonPackage(row.id, search.tenant_id)
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
          <span>Lesson Packages</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            New
          </el-button>
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
        <el-form-item label="Course ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable style="width: 130px;">
            <el-option label="Enabled" value="enabled" />
            <el-option label="Disabled" value="disabled" />
          </el-select>
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
        <el-table-column prop="code" label="Code" width="130" />
        <el-table-column prop="name" label="Name" min-width="160" />
        <el-table-column prop="course_name" label="Course" min-width="160" />
        <el-table-column prop="lesson_units" label="Lessons" width="100" />
        <el-table-column prop="bonus_units" label="Bonus" width="100" />
        <el-table-column label="Total" width="100">
          <template #default="{ row }">
            {{ row.total_units || computePackageTotal(row.lesson_units, row.bonus_units) }}
          </template>
        </el-table-column>
        <el-table-column prop="sale_price" label="Sale Price" width="120" />
        <el-table-column prop="validity_days" label="Validity" width="100" />
        <el-table-column prop="status" label="Status" width="100" />
        <el-table-column prop="updated_at" label="Updated" width="180" />
        <el-table-column label="Actions" fixed="right" width="240">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              Edit
            </el-button>
            <el-button v-if="canStatus" link type="primary" @click="changeStatus(row)">
              {{ row.status === 'enabled' ? 'Disable' : 'Enable' }}
            </el-button>
            <el-button v-if="canDelete" link type="danger" @click="removeRow(row)">
              Delete
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No lesson packages" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? 'New Lesson Package' : 'Edit Lesson Package'" width="640px">
      <LessonPackageForm :mode="dialogMode" :tenant-id="search.tenant_id" :campus-id="search.campus_id" :course-id="search.course_id" :data="current" @success="onFormSuccess" />
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

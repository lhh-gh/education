<script setup lang="ts">
import type { GuardianPageParams, GuardianRecord } from '../../api/academic/profile.ts'
import { deleteGuardian, pageGuardians, updateGuardianStatus } from '../../api/academic/profile.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import GuardianForm from './components/GuardianForm.vue'
import { useMessage } from '@/hooks/useMessage.ts'

defineOptions({ name: 'EducationAcademicGuardianList' })

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const current = ref<GuardianRecord | null>(null)
const errorText = ref('')
const rows = ref<GuardianRecord[]>([])
const total = ref(0)
const search = reactive<GuardianPageParams>(defaultSearch())

const canCreate = computed(() => hasAuth('education:academic:guardian:create'))
const canEdit = computed(() => hasAuth('education:academic:guardian:update'))
const canStatus = computed(() => hasAuth('education:academic:guardian:status'))
const canDelete = computed(() => hasAuth('education:academic:guardian:delete'))

function defaultSearch(): GuardianPageParams {
  return { page: 1, page_size: 20, tenant_id: undefined, keyword: '', gender: undefined, status: undefined }
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageGuardians(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Guardian list loading failed'
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

function openEdit(row: GuardianRecord) {
  dialogMode.value = 'edit'
  current.value = row
  dialogVisible.value = true
}

async function changeStatus(row: GuardianRecord) {
  await updateGuardianStatus(row.id, row.status === 'enabled' ? 'disabled' : 'enabled', search.tenant_id)
  await loadRows()
}

async function removeRow(row: GuardianRecord) {
  await message.confirm('Delete this guardian?')
  await deleteGuardian(row.id, search.tenant_id)
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
          <span>Guardians</span>
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
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable />
        </el-form-item>
        <el-form-item label="Gender">
          <el-select v-model="search.gender" clearable style="width: 130px;">
            <el-option label="Male" value="male" />
            <el-option label="Female" value="female" />
            <el-option label="Unknown" value="unknown" />
          </el-select>
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
        <el-table-column prop="name" label="Name" min-width="150" />
        <el-table-column prop="mobile" label="Mobile" width="150" />
        <el-table-column prop="gender" label="Gender" width="100" />
        <el-table-column prop="student_count" label="Students" width="100" />
        <el-table-column prop="openid" label="OpenID" min-width="160" />
        <el-table-column prop="unionid" label="UnionID" min-width="160" />
        <el-table-column prop="status" label="Status" width="100" />
        <el-table-column prop="updated_at" label="Updated" width="180" />
        <el-table-column label="Actions" fixed="right" width="220">
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
          <el-empty description="No guardians" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.page_size" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? 'New Guardian' : 'Edit Guardian'" width="560px">
      <GuardianForm :mode="dialogMode" :tenant-id="search.tenant_id" :data="current" @success="onFormSuccess" />
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

<script setup lang="ts">
import type { EducationRoleCode, UserProfilePageParams, UserProfileRecord } from '../../api/foundation/userProfile.ts'
import { pageUserProfiles, updateUserProfileStatus } from '../../api/foundation/userProfile.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { isPlatformRole } from './actionRules.ts'
import CampusScopeForm from './components/CampusScopeForm.vue'
import UserProfileForm from './components/UserProfileForm.vue'

defineOptions({ name: 'EducationFoundationUserProfileList' })

const message = useMessage()
const loading = ref(false)
const formVisible = ref(false)
const scopeVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const currentProfile = ref<UserProfileRecord | null>(null)
const scopeProfile = ref<UserProfileRecord | null>(null)
const errorText = ref('')
const rows = ref<UserProfileRecord[]>([])
const total = ref(0)
const search = reactive<UserProfilePageParams>(defaultSearch())

const roleOptions: Array<{ label: string, value: EducationRoleCode }> = [
  { label: 'Platform super admin', value: 'platform_super_admin' },
  { label: 'Platform operator', value: 'platform_operator' },
  { label: 'Tenant admin', value: 'tenant_admin' },
  { label: 'Principal', value: 'principal' },
  { label: 'Academic staff', value: 'academic_staff' },
  { label: 'Front desk', value: 'front_desk' },
  { label: 'Teacher', value: 'teacher' },
  { label: 'Finance', value: 'finance' },
  { label: 'Guardian', value: 'guardian' },
]

const canCreate = computed(() => hasAuth('education:foundation:user-profile:create'))
const canEdit = computed(() => hasAuth('education:foundation:user-profile:update'))
const canStatus = computed(() => hasAuth('education:foundation:user-profile:status'))
const canSaveCampusScope = computed(() => hasAuth('education:foundation:campus-scope:save'))

function defaultSearch(): UserProfilePageParams {
  return {
    page: 1,
    page_size: 20,
    tenant_id: undefined,
    keyword: '',
    role_code: undefined,
    status: undefined,
  }
}

async function loadProfiles() {
  loading.value = true
  try {
    const response = await pageUserProfiles(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Profile list loading failed'
    message.error(error?.message ?? 'Profile list loading failed')
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadProfiles()
}

function handleReset() {
  Object.assign(search, defaultSearch())
  loadProfiles()
}

function roleLabel(roleCode: EducationRoleCode): string {
  return roleOptions.find(item => item.value === roleCode)?.label ?? roleCode
}

function openCreate() {
  dialogMode.value = 'create'
  currentProfile.value = null
  formVisible.value = true
}

function openEdit(row: UserProfileRecord) {
  dialogMode.value = 'edit'
  currentProfile.value = row
  formVisible.value = true
}

function openCampusScope(row: UserProfileRecord) {
  scopeProfile.value = row
  scopeVisible.value = true
}

function canOpenCampusScope(row: UserProfileRecord): boolean {
  return canSaveCampusScope.value && !isPlatformRole(row.role_code)
}

async function changeStatus(row: UserProfileRecord) {
  const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
  await updateUserProfileStatus(row.id, nextStatus)
  await loadProfiles()
}

function onFormSuccess() {
  formVisible.value = false
  loadProfiles()
}

function onScopeSuccess() {
  scopeVisible.value = false
  loadProfiles()
}

onMounted(loadProfiles)
</script>

<template>
  <div class="mine-layout education-foundation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>User profiles</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            New profile
          </el-button>
        </div>
      </template>

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="Tenant ID">
          <el-input-number v-model="search.tenant_id" :controls="false" :min="1" />
        </el-form-item>
        <el-form-item label="Keyword">
          <el-input v-model="search.keyword" clearable placeholder="Name/mobile/OpenID/UnionID" />
        </el-form-item>
        <el-form-item label="Role">
          <el-select v-model="search.role_code" class="role-filter" clearable filterable placeholder="All">
            <el-option v-for="item in roleOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" class="status-filter" clearable placeholder="All">
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
        <el-table-column prop="display_name" label="Name" min-width="150" />
        <el-table-column prop="mobile" label="Mobile" width="140" />
        <el-table-column prop="role_code" label="Role" min-width="170">
          <template #default="{ row }">
            {{ roleLabel(row.role_code) }}
          </template>
        </el-table-column>
        <el-table-column prop="tenant_id" label="Tenant" width="100" />
        <el-table-column prop="current_campus_id" label="Campus" width="100" />
        <el-table-column prop="campus_scope_count" label="Scopes" width="100" />
        <el-table-column prop="status" label="Status" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
              {{ row.status === 'enabled' ? 'Enabled' : 'Disabled' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="Updated" width="180" />
        <el-table-column label="Actions" fixed="right" width="240">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              Edit
            </el-button>
            <el-button v-if="canStatus" link type="primary" @click="changeStatus(row)">
              {{ row.status === 'enabled' ? 'Disable' : 'Enable' }}
            </el-button>
            <el-button v-if="canOpenCampusScope(row)" link type="primary" @click="openCampusScope(row)">
              Scope
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No profiles" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.page_size"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadProfiles"
      />
    </el-card>

    <el-dialog v-model="formVisible" :title="dialogMode === 'create' ? 'New profile' : 'Edit profile'" width="640px">
      <UserProfileForm :key="currentProfile?.id ?? 'create'" :mode="dialogMode" :data="currentProfile" @success="onFormSuccess" />
    </el-dialog>

    <el-dialog v-model="scopeVisible" title="Campus scope" width="560px">
      <CampusScopeForm :profile="scopeProfile" :can-submit="canSaveCampusScope" @success="onScopeSuccess" />
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.education-foundation-page {
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .page-alert,
  .search-form {
    margin-bottom: 12px;
  }

  .role-filter {
    width: 180px;
  }

  .status-filter {
    width: 120px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

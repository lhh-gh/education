<script setup lang="ts">
import type { EducationRoleCode, UserProfilePageParams, UserProfileRecord } from '../../api/foundation/userProfile.ts'
import { pageUserProfiles, updateUserProfileStatus } from '../../api/foundation/userProfile.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { educationRoleLabel, educationRoleOptions, foundationStatusLabel, foundationStatusOptions, isPlatformRole } from './actionRules.ts'
import CampusScopeForm from './components/CampusScopeForm.vue'
import UserProfileForm from './components/UserProfileForm.vue'
import { useMessage } from '@/hooks/useMessage.ts'

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

const roleOptions = educationRoleOptions()
const statusOptions = foundationStatusOptions()

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
    errorText.value = error?.message ?? '教育用户档案加载失败'
    message.error(errorText.value)
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
  return educationRoleLabel(roleCode)
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
          <span>教育用户档案</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新增档案
          </el-button>
        </div>
      </template>

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="机构ID">
          <el-input-number v-model="search.tenant_id" :controls="false" :min="1" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable placeholder="姓名/手机号/OpenID/UnionID" />
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="search.role_code" class="role-filter" clearable filterable placeholder="全部">
            <el-option v-for="item in roleOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" class="status-filter" clearable placeholder="全部">
            <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value" />
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
        <el-table-column prop="display_name" label="姓名" min-width="150" />
        <el-table-column prop="mobile" label="手机号" width="140" />
        <el-table-column prop="role_code" label="角色" min-width="170">
          <template #default="{ row }">
            {{ roleLabel(row.role_code) }}
          </template>
        </el-table-column>
        <el-table-column prop="tenant_id" label="机构ID" width="100" />
        <el-table-column prop="current_campus_id" label="当前校区" width="100" />
        <el-table-column prop="campus_scope_count" label="校区范围" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
              {{ foundationStatusLabel(row.status) }}
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
              {{ row.status === 'enabled' ? '停用' : '启用' }}
            </el-button>
            <el-button v-if="canOpenCampusScope(row)" link type="primary" @click="openCampusScope(row)">
              校区范围
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无教育用户档案" />
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

    <el-dialog v-model="formVisible" :title="dialogMode === 'create' ? '新增档案' : '编辑档案'" width="640px">
      <UserProfileForm :key="currentProfile?.id ?? 'create'" :mode="dialogMode" :data="currentProfile" @success="onFormSuccess" />
    </el-dialog>

    <el-dialog v-model="scopeVisible" title="校区范围" width="560px">
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

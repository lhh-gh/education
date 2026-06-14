<script setup lang="ts">
import type { CampusPageParams, CampusRecord } from '../../api/foundation/campus.ts'
import { deleteCampus, pageCampuses, updateCampusStatus } from '../../api/foundation/campus.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { tenantRequired } from './actionRules.ts'
import CampusForm from './components/CampusForm.vue'

defineOptions({ name: 'EducationFoundationCampusList' })

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const currentCampus = ref<CampusRecord | null>(null)
const errorText = ref('')
const rows = ref<CampusRecord[]>([])
const total = ref(0)
const search = reactive<CampusPageParams>(defaultSearch())

const canCreate = computed(() => Boolean(search.tenant_id) && hasAuth('education:foundation:campus:create'))
const canEdit = computed(() => hasAuth('education:foundation:campus:update'))
const canStatus = computed(() => hasAuth('education:foundation:campus:status'))
const canDelete = computed(() => hasAuth('education:foundation:campus:delete'))
const tenantMissing = computed(() => tenantRequired(search.tenant_id))

function defaultSearch(): CampusPageParams {
  return {
    page: 1,
    page_size: 20,
    tenant_id: undefined,
    keyword: '',
    status: undefined,
  }
}

async function loadCampuses() {
  if (tenantMissing.value) {
    message.warning('请先选择机构')
    return
  }
  loading.value = true
  try {
    const response = await pageCampuses(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = error?.message ?? 'Campus list loading failed'
    message.error(error?.message ?? '校区列表加载失败')
  }
  finally {
    loading.value = false
  }
}

function handleSearch() {
  search.page = 1
  loadCampuses()
}

function handleReset() {
  Object.assign(search, defaultSearch())
  rows.value = []
  total.value = 0
  loadCampuses()
}

function openCreate() {
  if (tenantMissing.value) {
    return
  }
  dialogMode.value = 'create'
  currentCampus.value = null
  dialogVisible.value = true
}

function openEdit(row: CampusRecord) {
  dialogMode.value = 'edit'
  currentCampus.value = row
  dialogVisible.value = true
}

async function changeStatus(row: CampusRecord) {
  if (tenantMissing.value) {
    return
  }
  const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
  await updateCampusStatus(search.tenant_id as number, row.id, nextStatus)
  await loadCampuses()
}

async function removeCampus(row: CampusRecord) {
  if (tenantMissing.value) {
    return
  }
  await message.confirm('确认删除该校区？')
  await deleteCampus(search.tenant_id as number, row.id)
  await loadCampuses()
}

function onFormSuccess() {
  dialogVisible.value = false
  loadCampuses()
}
</script>

<template>
  <div class="mine-layout education-foundation-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>校区</span>
          <el-button type="primary" :disabled="!canCreate" @click="openCreate">
            新建校区
          </el-button>
        </div>
      </template>

      <el-alert
        v-if="tenantMissing"
        class="tenant-alert"
        type="warning"
        show-icon
        :closable="false"
        title="请选择机构后管理校区"
      />

      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="机构ID" required>
          <el-input-number v-model="search.tenant_id" :min="1" :controls="false" placeholder="X-Tenant-Id" />
        </el-form-item>
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable placeholder="校区名称/编码/手机号/地址" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable placeholder="全部" style="width: 120px;">
            <el-option label="启用" value="enabled" />
            <el-option label="停用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="tenantMissing" @click="handleSearch">
            查询
          </el-button>
          <el-button @click="handleReset">
            Reset
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="name" label="校区名称" min-width="160" />
        <el-table-column prop="code" label="编码" width="140" />
        <el-table-column prop="contact_name" label="联系人" width="120" />
        <el-table-column prop="contact_phone" label="联系电话" width="150" />
        <el-table-column prop="address" label="地址" min-width="200" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 'enabled' ? 'success' : 'info'">
              {{ row.status === 'enabled' ? '启用' : '停用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" fixed="right" width="230">
          <template #default="{ row }">
            <el-button v-if="canEdit" link type="primary" @click="openEdit(row)">
              编辑
            </el-button>
            <el-button v-if="canStatus" link type="primary" @click="changeStatus(row)">
              {{ row.status === 'enabled' ? '停用' : '启用' }}
            </el-button>
            <el-button v-if="canDelete" link type="danger" @click="removeCampus(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无校区" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.page_size"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadCampuses"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新建校区' : '编辑校区'" width="560px">
      <CampusForm
        v-if="search.tenant_id"
        :mode="dialogMode"
        :tenant-id="search.tenant_id"
        :data="currentCampus"
        @success="onFormSuccess"
      />
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

  .tenant-alert {
    margin-bottom: 12px;
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

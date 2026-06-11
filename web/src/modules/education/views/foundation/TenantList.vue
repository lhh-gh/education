<script setup lang="ts">
import type { TenantRecord, TenantPageParams } from '../../api/foundation/tenant.ts'
import { deleteTenant, pageTenants, updateTenantStatus } from '../../api/foundation/tenant.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import TenantForm from './components/TenantForm.vue'

defineOptions({ name: 'EducationFoundationTenantList' })

const message = useMessage()
const loading = ref(false)
const dialogVisible = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const currentTenant = ref<TenantRecord | null>(null)
const rows = ref<TenantRecord[]>([])
const total = ref(0)
const search = reactive<TenantPageParams>({
  page: 1,
  page_size: 20,
  keyword: '',
  status: undefined,
})

const canCreate = computed(() => hasAuth('education:foundation:tenant:create'))
const canEdit = computed(() => hasAuth('education:foundation:tenant:update'))
const canStatus = computed(() => hasAuth('education:foundation:tenant:status'))
const canDelete = computed(() => hasAuth('education:foundation:tenant:delete'))

async function loadTenants() {
  loading.value = true
  try {
    const response = await pageTenants(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  catch (error: any) {
    message.error(error?.message ?? '机构列表加载失败')
  }
  finally {
    loading.value = false
  }
}

function openCreate() {
  dialogMode.value = 'create'
  currentTenant.value = null
  dialogVisible.value = true
}

function openEdit(row: TenantRecord) {
  dialogMode.value = 'edit'
  currentTenant.value = row
  dialogVisible.value = true
}

async function changeStatus(row: TenantRecord) {
  const nextStatus = row.status === 'enabled' ? 'disabled' : 'enabled'
  await updateTenantStatus(row.id, nextStatus)
  await loadTenants()
}

async function removeTenant(row: TenantRecord) {
  await message.confirm('确认删除该机构？')
  await deleteTenant(row.id)
  await loadTenants()
}

function onFormSuccess() {
  dialogVisible.value = false
  loadTenants()
}

onMounted(loadTenants)
</script>

<template>
  <div class="mine-layout pt-3 education-foundation-page">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>机构</span>
          <el-button v-if="canCreate" type="primary" @click="openCreate">
            新建机构
          </el-button>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="关键字">
          <el-input v-model="search.keyword" clearable placeholder="机构名称/编码/手机号" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable placeholder="全部" style="width: 120px">
            <el-option label="启用" value="enabled" />
            <el-option label="停用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadTenants">
            查询
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="name" label="机构名称" min-width="160" />
        <el-table-column prop="code" label="编码" width="140" />
        <el-table-column prop="short_name" label="简称" width="140" />
        <el-table-column prop="contact_name" label="联系人" width="120" />
        <el-table-column prop="contact_phone" label="联系电话" width="150" />
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
            <el-button v-if="canDelete" link type="danger" @click="removeTenant(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无机构" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.page_size"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadTenants"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogMode === 'create' ? '新建机构' : '编辑机构'" width="560px">
      <TenantForm :mode="dialogMode" :data="currentTenant" @success="onFormSuccess" />
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

  .search-form {
    margin-bottom: 12px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

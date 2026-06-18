<script setup lang="ts">
import type { DataPermissionRecord } from '../../api/group/permission.ts'
import { getUserDataScopePreview, pageDataPermissions } from '../../api/group/permission.ts'
import DataPermissionForm from './components/DataPermissionForm.vue'
import { dataScopePreviewText, groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupDataPermissionList' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<DataPermissionRecord[]>([])
const total = ref(0)
const previewText = ref('No campus scope')
const search = reactive({ page: 1, pageSize: 20, user_id: undefined as number | undefined })

async function loadRows() {
  loading.value = true
  try {
    const [page, preview] = await Promise.all([pageDataPermissions(search), getUserDataScopePreview(search)])
    rows.value = page.data.list
    total.value = page.data.total
    previewText.value = dataScopePreviewText(preview.data)
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" :title="`Allowed campuses: ${previewText}`" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Data Permissions</span>
          <el-button type="primary" @click="formVisible = true">
            Assign Scope
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="user_id" label="User" width="120" />
        <el-table-column prop="scope_type" label="Scope" min-width="160" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No data permissions" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <DataPermissionForm v-model="formVisible" @success="loadRows" />
  </div>
</template>

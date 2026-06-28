<script setup lang="ts">
import type { DataPermissionRecord } from '../../api/group/permission.ts'
import { getUserDataScopePreview, pageDataPermissions } from '../../api/group/permission.ts'
import DataPermissionForm from './components/DataPermissionForm.vue'
import { dataScopePreviewText, groupScopeTypeLabel, groupStatusLabel, groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupDataPermissionList' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<DataPermissionRecord[]>([])
const total = ref(0)
const previewText = ref('暂无校区范围')
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
    <el-alert class="mb-3" type="info" :title="`可访问校区：${previewText}`" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>数据权限</span>
          <el-button type="primary" @click="formVisible = true">
            分配范围
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="user_id" label="用户" width="120" />
        <el-table-column label="范围" min-width="160">
          <template #default="{ row }">
            {{ groupScopeTypeLabel(row.scope_type) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ groupStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无数据权限" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <DataPermissionForm v-model="formVisible" @success="loadRows" />
  </div>
</template>

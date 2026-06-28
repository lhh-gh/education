<script setup lang="ts">
import type { MaterialVersionPayload } from '../../api/content/version.ts'
import type { MaterialVersionRow } from '../../api/content/types.ts'
import { createMaterialVersion, pageMaterialVersions } from '../../api/content/version.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { contentStatusLabel, contentStatusTag } from './contentRules.ts'
import MaterialVersionDrawer from './components/MaterialVersionDrawer.vue'

defineOptions({ name: 'EducationContentMaterialVersionList' })

const loading = ref(false)
const rows = ref<MaterialVersionRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, material_id: undefined as number | undefined })
const drawerVisible = ref(false)
const form = reactive<MaterialVersionPayload>({ title: '', content: '' })
const canCreate = computed(() => hasAuth('education:content:version:create'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageMaterialVersions(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function resetSearch() {
  search.page = 1
  search.material_id = undefined
  loadRows()
}

function openDrawer() {
  form.title = ''
  form.content = ''
  drawerVisible.value = true
}

async function createVersion() {
  if (!search.material_id) {
    return
  }
  await createMaterialVersion(search.material_id, form)
  drawerVisible.value = false
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>资料版本</span>
          <el-button v-if="canCreate" type="primary" @click="openDrawer">
            新建版本
          </el-button>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="资料 ID">
          <el-input-number v-model="search.material_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
          <el-button @click="resetSearch">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="material_id" label="资料 ID" width="120" />
        <el-table-column prop="version_no" label="版本号" width="120" />
        <el-table-column prop="title" label="版本标题" min-width="180" />
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="contentStatusTag(row.status)">
              {{ contentStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="published_at" label="发布时间" width="180" />
        <template #empty>
          <el-empty description="暂无资料版本" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadRows"
      />
    </el-card>

    <MaterialVersionDrawer v-model:visible="drawerVisible" v-model:model="form" :material-id="search.material_id">
      <template #footer>
        <el-button v-if="canCreate" type="primary" @click="createVersion">
          保存草稿版本
        </el-button>
      </template>
    </MaterialVersionDrawer>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

<script setup lang="ts">
import type { MaterialAttachmentRow } from '../../api/content/types.ts'
import { pageMaterialAttachments } from '../../api/content/attachment.ts'

defineOptions({ name: 'EducationContentMaterialAttachmentList' })

const loading = ref(false)
const rows = ref<MaterialAttachmentRow[]>([])
const total = ref(0)
const search = reactive({
  page: 1,
  pageSize: 20,
  material_version_id: undefined as number | undefined,
  file_type: '',
})

const fileTypeOptions = [
  { label: 'PDF', value: 'pdf' },
  { label: '图片', value: 'image' },
  { label: '视频', value: 'video' },
  { label: '文档', value: 'document' },
  { label: '其他', value: 'other' },
]

function fileTypeLabel(value: string) {
  return fileTypeOptions.find(item => item.value === value)?.label ?? value
}

function fileSizeLabel(value: number) {
  if (value >= 1024 * 1024) {
    return `${(value / 1024 / 1024).toFixed(2)} MB`
  }
  if (value >= 1024) {
    return `${(value / 1024).toFixed(2)} KB`
  }

  return `${value} B`
}

function resetSearch() {
  search.page = 1
  search.material_version_id = undefined
  search.file_type = ''
  loadRows()
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageMaterialAttachments(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>资料附件</span>
        </div>
      </template>

      <el-alert class="mb-3" title="附件访问范围跟随资料版本权限。" type="info" :closable="false" />

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="版本 ID">
          <el-input-number v-model="search.material_version_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="文件类型">
          <el-select v-model="search.file_type" clearable class="filter-select">
            <el-option v-for="item in fileTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
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
        <el-table-column prop="file_name" label="文件名" min-width="180" />
        <el-table-column prop="material_version_id" label="版本 ID" width="120" />
        <el-table-column label="类型" width="120">
          <template #default="{ row }">
            {{ fileTypeLabel(row.file_type) }}
          </template>
        </el-table-column>
        <el-table-column label="大小" width="120">
          <template #default="{ row }">
            {{ fileSizeLabel(row.file_size) }}
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="90" />
        <el-table-column label="文件地址" min-width="220">
          <template #default="{ row }">
            <el-link :href="row.file_url" target="_blank" type="primary">
              {{ row.file_url }}
            </el-link>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无附件" />
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
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .filter-select {
    width: 140px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

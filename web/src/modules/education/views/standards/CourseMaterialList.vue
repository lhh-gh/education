<script setup lang="ts">
import type { CourseMaterialPayload, CourseMaterialRecord } from '../../api/standards/material.ts'
import { pageCourseMaterials, saveCourseMaterial } from '../../api/standards/material.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

defineOptions({ name: 'EducationStandardsCourseMaterialList' })

const loading = ref(false)
const saving = ref(false)
const rows = ref<CourseMaterialRecord[]>([])
const total = ref(0)
const search = reactive({
  page: 1,
  pageSize: 20,
  course_id: undefined as number | undefined,
  material_type: '',
  status: '',
})
const form = reactive<CourseMaterialPayload>({
  material_code: '',
  material_name: '',
  course_id: undefined,
  material_type: 'file',
  file_url: '',
  guardian_visible: false,
})
const canSave = computed(() => hasAuth('education:standards:material:save'))

const materialTypeOptions = [
  { label: '文件', value: 'file' },
  { label: '视频', value: 'video' },
  { label: '课件', value: 'courseware' },
  { label: '练习', value: 'worksheet' },
]
const statusOptions = [
  { label: '草稿', value: 'draft', tag: 'info' },
  { label: '审核中', value: 'reviewing', tag: 'warning' },
  { label: '已发布', value: 'published', tag: 'success' },
  { label: '已撤回', value: 'withdrawn', tag: 'info' },
  { label: '已归档', value: 'archived', tag: 'info' },
] as const

function materialTypeLabel(value: string) {
  return materialTypeOptions.find(item => item.value === value)?.label ?? value
}

function statusMeta(value: string) {
  return statusOptions.find(item => item.value === value) ?? { label: value, tag: 'info' }
}

function resetSearch() {
  search.page = 1
  search.course_id = undefined
  search.material_type = ''
  search.status = ''
  loadRows()
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageCourseMaterials(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  try {
    await saveCourseMaterial(form)
    form.material_code = ''
    form.material_name = ''
    form.course_id = undefined
    form.file_url = ''
    form.guardian_visible = false
    search.page = 1
    await loadRows()
  }
  finally {
    saving.value = false
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-standards-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>课程资料</span>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="课程 ID">
          <el-input-number v-model="search.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="资料类型">
          <el-select v-model="search.material_type" clearable class="filter-select">
            <el-option v-for="item in materialTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable class="filter-select">
            <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value" />
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

      <el-form v-if="canSave" :inline="true" :model="form" class="save-form">
        <el-form-item label="资料编码">
          <el-input v-model="form.material_code" clearable placeholder="请输入资料编码" />
        </el-form-item>
        <el-form-item label="资料名称">
          <el-input v-model="form.material_name" clearable placeholder="请输入资料名称" />
        </el-form-item>
        <el-form-item label="课程 ID">
          <el-input-number v-model="form.course_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="资料类型">
          <el-select v-model="form.material_type" class="filter-select">
            <el-option v-for="item in materialTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="文件地址">
          <el-input v-model="form.file_url" clearable placeholder="请输入文件地址" />
        </el-form-item>
        <el-form-item label="家长可见">
          <el-switch v-model="form.guardian_visible" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="saving" @click="save">
            保存资料
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="material_code" label="资料编码" width="150" />
        <el-table-column prop="material_name" label="资料名称" min-width="180" />
        <el-table-column prop="course_id" label="课程 ID" width="120" />
        <el-table-column label="资料类型" width="120">
          <template #default="{ row }">
            {{ materialTypeLabel(row.material_type) }}
          </template>
        </el-table-column>
        <el-table-column label="家长可见" width="120">
          <template #default="{ row }">
            <el-tag :type="row.guardian_visible ? 'success' : 'info'">
              {{ row.guardian_visible ? '可见' : '不可见' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="statusMeta(row.status).tag">
              {{ statusMeta(row.status).label }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="file_url" label="文件地址" min-width="220" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无课程资料" />
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
.education-standards-page {
  .filter-select {
    width: 140px;
  }

  .save-form {
    margin-bottom: 16px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

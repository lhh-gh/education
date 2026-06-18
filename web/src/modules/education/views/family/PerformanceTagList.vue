<script setup lang="ts">
import type { PerformanceTagRecord } from '../../api/family/comment.ts'
import { pagePerformanceTags, savePerformanceTag } from '../../api/family/comment.ts'
import { familyTagType } from './familyRules.ts'

defineOptions({ name: 'EducationFamilyPerformanceTagList' })

const loading = ref(false)
const rows = ref<PerformanceTagRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, tag_type: '' })
const form = reactive({ tag_code: '', tag_name: '', tag_type: 'attitude' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pagePerformanceTags(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function createTag() {
  await savePerformanceTag({ ...form })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Performance Tags</span>
          <el-button type="primary" @click="createTag">
            Save
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.tag_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.tag_name" />
        </el-form-item>
        <el-form-item label="Type">
          <el-input v-model="form.tag_type" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="tag_code" label="Code" width="160" />
        <el-table-column prop="tag_name" label="Name" min-width="180" />
        <el-table-column prop="tag_type" label="Type" width="140" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No tags" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

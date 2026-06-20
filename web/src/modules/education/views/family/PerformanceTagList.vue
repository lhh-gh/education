<script setup lang="ts">
import type { PerformanceTagRecord } from '../../api/family/comment.ts'
import { pagePerformanceTags, savePerformanceTag } from '../../api/family/comment.ts'
import { familyRecordTypeLabel, familyStatusLabel, familyTagType } from './familyRules.ts'

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
          <span>表现标签</span>
          <el-button type="primary" @click="createTag">
            保存
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="编码">
          <el-input v-model="form.tag_code" />
        </el-form-item>
        <el-form-item label="名称">
          <el-input v-model="form.tag_name" />
        </el-form-item>
        <el-form-item label="类型">
          <el-input v-model="form.tag_type" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="tag_code" label="编码" width="160" />
        <el-table-column prop="tag_name" label="名称" min-width="180" />
        <el-table-column label="类型" width="140">
          <template #default="{ row }">
            {{ familyRecordTypeLabel(row.tag_type) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ familyStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无表现标签" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

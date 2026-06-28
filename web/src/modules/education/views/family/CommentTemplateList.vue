<script setup lang="ts">
import type { CommentTemplateRecord } from '../../api/family/comment.ts'
import { pageCommentTemplates, saveCommentTemplate } from '../../api/family/comment.ts'
import { familyStatusLabel, familyTagType } from './familyRules.ts'

defineOptions({ name: 'EducationFamilyCommentTemplateList' })

const loading = ref(false)
const rows = ref<CommentTemplateRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })
const form = reactive({ template_code: '', template_name: '', content: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageCommentTemplates(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function createTemplate() {
  await saveCommentTemplate({ ...form })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>评语模板</span>
          <el-button type="primary" @click="createTemplate">
            保存
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="编码">
          <el-input v-model="form.template_code" />
        </el-form-item>
        <el-form-item label="名称">
          <el-input v-model="form.template_name" />
        </el-form-item>
        <el-form-item label="内容">
          <el-input v-model="form.content" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="template_code" label="编码" width="160" />
        <el-table-column prop="template_name" label="名称" width="180" />
        <el-table-column prop="content" label="内容" min-width="240" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ familyStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无评语模板" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

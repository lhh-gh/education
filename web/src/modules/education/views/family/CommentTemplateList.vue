<script setup lang="ts">
import type { CommentTemplateRecord } from '../../api/family/comment.ts'
import { pageCommentTemplates, saveCommentTemplate } from '../../api/family/comment.ts'
import { familyTagType } from './familyRules.ts'

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
          <span>Comment Templates</span>
          <el-button type="primary" @click="createTemplate">
            Save
          </el-button>
        </div>
      </template>
      <el-form inline>
        <el-form-item label="Code">
          <el-input v-model="form.template_code" />
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="form.template_name" />
        </el-form-item>
        <el-form-item label="Content">
          <el-input v-model="form.content" />
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="template_code" label="Code" width="160" />
        <el-table-column prop="template_name" label="Name" width="180" />
        <el-table-column prop="content" label="Content" min-width="240" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No templates" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

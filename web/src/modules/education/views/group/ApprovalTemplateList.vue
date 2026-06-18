<script setup lang="ts">
import type { ApprovalTemplateRecord } from '../../api/group/approval.ts'
import { pageApprovalTemplates } from '../../api/group/approval.ts'
import ApprovalTemplateEditor from './components/ApprovalTemplateEditor.vue'
import { groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupApprovalTemplateList' })

const loading = ref(false)
const editorVisible = ref(false)
const rows = ref<ApprovalTemplateRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20 })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageApprovalTemplates(search)
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
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Current user data scope applies to approval data" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Approval Templates</span>
          <el-button type="primary" @click="editorVisible = true">
            New Template
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="template_code" label="Code" width="160" />
        <el-table-column prop="template_name" label="Name" min-width="180" />
        <el-table-column prop="business_type" label="Business" width="140" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No approval templates" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ApprovalTemplateEditor v-model="editorVisible" @success="loadRows" />
  </div>
</template>

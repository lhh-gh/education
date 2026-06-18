<script setup lang="ts">
import type { AdmissionTaskRecord } from '../../api/admissions/task.ts'
import { pageAdmissionTasks } from '../../api/admissions/task.ts'
import { admissionTagType } from './admissionRules.ts'

defineOptions({ name: 'EducationAdmissionTaskList' })

const loading = ref(false)
const rows = ref<AdmissionTaskRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, tenant_id: undefined as number | undefined, campus_id: undefined as number | undefined, status: undefined as string | undefined })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageAdmissionTasks(search)
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
  <div class="mine-layout admission-page pt-3">
    <el-card shadow="never">
      <template #header><div class="page-header"><span>Admission Tasks</span><el-button @click="loadRows">Refresh</el-button></div></template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column prop="assignee_user_id" label="Assignee" width="120" />
        <el-table-column prop="due_at" label="Due" width="180" />
        <el-table-column label="Status" width="120"><template #default="{ row }"><el-tag :type="admissionTagType(row.status)">{{ row.status }}</el-tag></template></el-table-column>
        <template #empty><el-empty description="No admission tasks" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

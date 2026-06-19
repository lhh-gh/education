<script setup lang="ts">
import type { AdmissionTaskRecord } from '../../api/admissions/task.ts'
import { pageAdmissionTasks } from '../../api/admissions/task.ts'
import { admissionStatusLabel, admissionTagType } from './admissionRules.ts'

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
      <template #header><div class="page-header"><span>招生任务</span><el-button @click="loadRows">刷新</el-button></div></template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="任务标题" min-width="180" />
        <el-table-column prop="assignee_user_id" label="负责人" width="120" />
        <el-table-column prop="due_at" label="截止时间" width="180" />
        <el-table-column label="状态" width="120"><template #default="{ row }"><el-tag :type="admissionTagType(row.status)">{{ admissionStatusLabel(row.status) }}</el-tag></template></el-table-column>
        <template #empty><el-empty description="暂无招生任务" /></template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

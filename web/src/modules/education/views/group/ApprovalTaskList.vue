<script setup lang="ts">
import type { ApprovalTaskRecord } from '../../api/group/approval.ts'
import { completeApprovalTask, pageApprovalTasks } from '../../api/group/approval.ts'
import { canCompleteApprovalTask, groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupApprovalTaskList' })

const loading = ref(false)
const rows = ref<ApprovalTaskRecord[]>([])
const total = ref(0)
const currentUserId = ref(0)
const hasOverride = ref(false)
const search = reactive({ page: 1, pageSize: 20, status: 'pending' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageApprovalTasks(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function approve(row: ApprovalTaskRecord) {
  await completeApprovalTask(row.id, { result: 'approved', comment: 'ok' })
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="Assigned users and override users can complete pending tasks" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Approval Tasks</span>
          <el-button @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="id" label="Task" width="100" />
        <el-table-column prop="approval_instance_id" label="Instance" width="120" />
        <el-table-column prop="assignee_user_id" label="Assignee" width="120" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Action" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="!canCompleteApprovalTask(row, currentUserId, hasOverride)" @click="approve(row)">
              Complete
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No approval tasks" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

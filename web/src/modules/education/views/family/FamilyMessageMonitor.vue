<script setup lang="ts">
import type { FamilyMessageRecord } from '../../api/family/message.ts'
import { pageFamilyMessages } from '../../api/family/message.ts'
import { familyTagType } from './familyRules.ts'
import FamilyMessageThreadDrawer from './components/FamilyMessageThreadDrawer.vue'

defineOptions({ name: 'EducationFamilyMessageMonitor' })

const loading = ref(false)
const drawerVisible = ref(false)
const rows = ref<FamilyMessageRecord[]>([])
const total = ref(0)
const activeThread = ref<{ student_id: number, thread_id: string }>()
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageFamilyMessages(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function openThread(row: FamilyMessageRecord) {
  activeThread.value = { student_id: row.student_id, thread_id: row.thread_id }
  drawerVisible.value = true
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Family Messages</span>
          <el-button :loading="loading" @click="loadRows">
            Refresh
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="thread_id" label="Thread" width="180" />
        <el-table-column prop="student_id" label="Student" width="120" />
        <el-table-column prop="sender_type" label="Sender" width="120" />
        <el-table-column prop="content" label="Content" min-width="240" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Action" width="100">
          <template #default="{ row }">
            <el-button link type="primary" @click="openThread(row)">
              Open
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No messages" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <FamilyMessageThreadDrawer v-model="drawerVisible" :thread="activeThread" />
  </div>
</template>

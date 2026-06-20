<script setup lang="ts">
import type { FamilyMessageRecord } from '../../api/family/message.ts'
import { pageFamilyMessages } from '../../api/family/message.ts'
import { familySenderLabel, familyStatusLabel, familyTagType } from './familyRules.ts'
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
          <span>家校消息</span>
          <el-button :loading="loading" @click="loadRows">
            刷新
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="thread_id" label="会话" width="180" />
        <el-table-column prop="student_id" label="学员" width="120" />
        <el-table-column label="发送方" width="120">
          <template #default="{ row }">
            {{ familySenderLabel(row.sender_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="content" label="内容" min-width="240" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ familyStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template #default="{ row }">
            <el-button link type="primary" @click="openThread(row)">
              打开
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无消息" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <FamilyMessageThreadDrawer v-model="drawerVisible" :thread="activeThread" />
  </div>
</template>

<script setup lang="ts">
import type { HomeworkAssignmentRecord } from '../../api/family/homework.ts'
import { pageHomeworkAssignments } from '../../api/family/homework.ts'
import { familyStatusLabel, familyTagType, targetCountLabel } from './familyRules.ts'
import HomeworkAssignmentForm from './components/HomeworkAssignmentForm.vue'

defineOptions({ name: 'EducationFamilyHomeworkAssignmentList' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<HomeworkAssignmentRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageHomeworkAssignments(search)
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
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>课后作业</span>
          <el-button type="primary" @click="formVisible = true">
            新增作业
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="标题" min-width="220" />
        <el-table-column label="对象" width="130">
          <template #default="{ row }">
            {{ targetCountLabel(row) }}
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
          <el-empty description="暂无课后作业" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <HomeworkAssignmentForm v-model="formVisible" @success="loadRows" />
  </div>
</template>

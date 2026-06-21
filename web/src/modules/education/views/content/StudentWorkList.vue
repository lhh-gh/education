<script setup lang="ts">
import type { StudentWorkRow } from '../../api/content/types.ts'
import { pageStudentWorks, publishStudentWork, withdrawStudentWork } from '../../api/content/student-work.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { contentPublishStatusOptions, contentStatusLabel, contentStatusTag } from './contentRules.ts'

defineOptions({ name: 'EducationContentStudentWorkList' })

const loading = ref(false)
const rows = ref<StudentWorkRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined, status: '' })
const canPublish = computed(() => hasAuth('education:content:student-work:publish'))
const canWithdraw = computed(() => hasAuth('education:content:student-work:withdraw'))

async function loadRows() {
  loading.value = true
  try {
    const response = await pageStudentWorks(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

function resetSearch() {
  search.page = 1
  search.student_id = undefined
  search.status = ''
  loadRows()
}

async function publish(row: StudentWorkRow) {
  await publishStudentWork(row.id)
  await loadRows()
}

async function withdraw(row: StudentWorkRow) {
  await withdrawStudentWork(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-content-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>学生作品</span>
        </div>
      </template>

      <el-form :inline="true" :model="search" class="search-form">
        <el-form-item label="学生 ID">
          <el-input-number v-model="search.student_id" :min="1" :controls="false" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable class="filter-select">
            <el-option v-for="item in contentPublishStatusOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
          <el-button @click="resetSearch">
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="作品标题" min-width="180" />
        <el-table-column prop="student_id" label="学生 ID" width="120" />
        <el-table-column prop="teacher_id" label="教师 ID" width="120" />
        <el-table-column label="状态" width="130">
          <template #default="{ row }">
            <el-tag :type="contentStatusTag(row.status)">
              {{ contentStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="published_at" label="发布时间" width="180" />
        <el-table-column label="操作" width="180">
          <template #default="{ row }">
            <el-button v-if="canPublish" link type="primary" @click="publish(row)">
              发布
            </el-button>
            <el-button v-if="canWithdraw" link type="warning" @click="withdraw(row)">
              撤回
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无学生作品" />
        </template>
      </el-table>

      <el-pagination
        v-model:current-page="search.page"
        v-model:page-size="search.pageSize"
        class="page-pagination"
        layout="total, sizes, prev, pager, next"
        :total="total"
        @change="loadRows"
      />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-content-page {
  .filter-select {
    width: 140px;
  }

  .page-pagination {
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>

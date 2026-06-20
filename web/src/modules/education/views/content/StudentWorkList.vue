<script setup lang="ts">
import type { StudentWorkRow } from '../../api/content/types.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { pageStudentWorks, publishStudentWork, withdrawStudentWork } from '../../api/content/student-work.ts'

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
  <div class="mine-layout pt-3">
    <el-card shadow="never">
      <template #header>
        <span>学生作品</span>
      </template>
      <el-form inline>
        <el-form-item label="学生 ID">
          <el-input-number v-model="search.student_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="search.status" clearable class="w-36">
            <el-option label="草稿" value="draft" />
            <el-option label="已发布" value="published" />
            <el-option label="已撤回" value="withdrawn" />
          </el-select>
        </el-form-item>
        <el-button type="primary" @click="loadRows">
          查询
        </el-button>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="作品标题" min-width="180" />
        <el-table-column prop="student_id" label="学生" width="120" />
        <el-table-column prop="teacher_id" label="教师" width="120" />
        <el-table-column prop="status" label="状态" width="130" />
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
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
  </div>
</template>

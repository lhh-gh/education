<script setup lang="ts">
import type { StudentWorkRow } from '../../api/content/types.ts'
import { pageStudentWorks, publishStudentWork, withdrawStudentWork } from '../../api/content/student-work.ts'

defineOptions({ name: 'EducationContentStudentWorkList' })

const loading = ref(false)
const rows = ref<StudentWorkRow[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, student_id: undefined as number | undefined, status: '' })

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
        <span>Student Works</span>
      </template>
      <el-form inline>
        <el-form-item label="Student ID">
          <el-input-number v-model="search.student_id" :min="1" controls-position="right" />
        </el-form-item>
        <el-form-item label="Status">
          <el-select v-model="search.status" clearable class="w-36">
            <el-option label="Draft" value="draft" />
            <el-option label="Published" value="published" />
            <el-option label="Withdrawn" value="withdrawn" />
          </el-select>
        </el-form-item>
        <el-button type="primary" @click="loadRows">
          Search
        </el-button>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="title" label="Title" min-width="180" />
        <el-table-column prop="student_id" label="Student" width="120" />
        <el-table-column prop="teacher_id" label="Teacher" width="120" />
        <el-table-column prop="status" label="Status" width="130" />
        <el-table-column label="Actions" width="180">
          <template #default="{ row }">
            <el-button link type="primary" @click="publish(row)">
              Publish
            </el-button>
            <el-button link type="warning" @click="withdraw(row)">
              Withdraw
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No student works" />
        </template>
      </el-table>
      <el-pagination class="mt-4 justify-end" layout="total" :total="total" />
    </el-card>
  </div>
</template>

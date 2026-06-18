<script setup lang="ts">
import type { LearningReportRecord } from '../../api/family/report.ts'
import { pageLearningReports, publishLearningReport, withdrawLearningReport } from '../../api/family/report.ts'
import { familyTagType, guardianVisibleMarker } from './familyRules.ts'
import LearningReportEditor from './components/LearningReportEditor.vue'

defineOptions({ name: 'EducationFamilyLearningReportList' })

const loading = ref(false)
const editorVisible = ref(false)
const rows = ref<LearningReportRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageLearningReports(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function publish(row: LearningReportRecord) {
  await publishLearningReport(row.id)
  await loadRows()
}

async function withdraw(row: LearningReportRecord) {
  await withdrawLearningReport(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-family-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Learning Reports</span>
          <el-button type="primary" @click="editorVisible = true">
            New Report
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="report_title" label="Title" min-width="220" />
        <el-table-column prop="report_period" label="Period" width="130" />
        <el-table-column label="Status" width="150">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Visibility" width="150">
          <template #default="{ row }">
            {{ guardianVisibleMarker(row) }}
          </template>
        </el-table-column>
        <el-table-column label="Action" width="160">
          <template #default="{ row }">
            <el-button link type="primary" @click="publish(row)">
              Publish
            </el-button>
            <el-button link type="danger" @click="withdraw(row)">
              Withdraw
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No reports" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <LearningReportEditor v-model="editorVisible" @success="loadRows" />
  </div>
</template>

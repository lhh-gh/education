<script setup lang="ts">
import type { LearningReportRecord } from '../../api/family/report.ts'
import { pageLearningReports, publishLearningReport, withdrawLearningReport } from '../../api/family/report.ts'
import { familyStatusLabel, familyTagType, guardianVisibleMarker } from './familyRules.ts'
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
          <span>学习报告</span>
          <el-button type="primary" @click="editorVisible = true">
            新增报告
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="report_title" label="标题" min-width="220" />
        <el-table-column prop="report_period" label="周期" width="130" />
        <el-table-column label="状态" width="150">
          <template #default="{ row }">
            <el-tag :type="familyTagType(row.status)">
              {{ familyStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="可见性" width="150">
          <template #default="{ row }">
            {{ guardianVisibleMarker(row) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160">
          <template #default="{ row }">
            <el-button link type="primary" @click="publish(row)">
              发布
            </el-button>
            <el-button link type="danger" @click="withdraw(row)">
              撤回
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无学习报告" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <LearningReportEditor v-model="editorVisible" @success="loadRows" />
  </div>
</template>

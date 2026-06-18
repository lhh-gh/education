<script setup lang="ts">
import type { DataQuestionLogRecord, MetricCatalogRecord } from '../../api/ai/data-question.ts'
import { askDataQuestion, pageDataQuestionLogs, pageMetricCatalogs } from '../../api/ai/data-question.ts'
import { aiErrorTitle, aiTagType, containsRawSql } from './aiRules.ts'

defineOptions({ name: 'EducationAiDataQuestionWorkbench' })

const loading = ref(false)
const logs = ref<DataQuestionLogRecord[]>([])
const metrics = ref<MetricCatalogRecord[]>([])
const total = ref(0)
const errorText = ref('')
const answerText = ref('')
const search = reactive({ page: 1, pageSize: 20 })
const form = reactive({ question_text: '', metric_codes: [] as string[] })

async function loadRows() {
  loading.value = true
  try {
    const [logResponse, metricResponse] = await Promise.all([
      pageDataQuestionLogs(search),
      pageMetricCatalogs({ page: 1, pageSize: 100 }),
    ])
    logs.value = logResponse.data.list
    total.value = logResponse.data.total
    metrics.value = metricResponse.data.list
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'Data questions loading failed'
  }
  finally {
    loading.value = false
  }
}

async function askQuestion() {
  if (containsRawSql(form.question_text)) {
    errorText.value = 'Validation failed'
    return
  }

  const response = await askDataQuestion({ ...form })
  answerText.value = response.data.answer_text
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Data Q&A</span>
          <el-button type="primary" @click="askQuestion">
            Ask
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form>
        <el-form-item label="Question">
          <el-input v-model="form.question_text" type="textarea" :rows="3" />
        </el-form-item>
        <el-form-item label="Metrics">
          <el-select v-model="form.metric_codes" multiple filterable style="width: 420px;">
            <el-option v-for="metric in metrics" :key="metric.metric_code" :label="metric.metric_name" :value="metric.metric_code" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-alert v-if="answerText" class="page-alert" type="success" show-icon :closable="true" :title="answerText" @close="answerText = ''" />
      <el-table v-loading="loading" :data="logs" row-key="id">
        <el-table-column prop="question_text" label="Question" min-width="240" />
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="answer_text" label="Answer" min-width="260" show-overflow-tooltip />
        <template #empty>
          <el-empty description="No data questions" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header { display: flex; align-items: center; justify-content: space-between; font-weight: 600; }
  .page-alert { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

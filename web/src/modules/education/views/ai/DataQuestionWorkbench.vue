<script setup lang="ts">
import type { DataQuestionLogRecord, MetricCatalogRecord } from '../../api/ai/data-question.ts'
import { askDataQuestion, pageDataQuestionLogs, pageMetricCatalogs } from '../../api/ai/data-question.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { aiErrorTitle, aiStatusLabel, aiTagType, containsRawSql } from './aiRules.ts'

defineOptions({ name: 'EducationAiDataQuestionWorkbench' })

const loading = ref(false)
const logs = ref<DataQuestionLogRecord[]>([])
const metrics = ref<MetricCatalogRecord[]>([])
const total = ref(0)
const errorText = ref('')
const answerText = ref('')
const search = reactive({ page: 1, pageSize: 20 })
const form = reactive({ question_text: '', metric_codes: [] as string[] })
const canAsk = computed(() => hasAuth('education:ai:data-question:create'))

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
    errorText.value = aiErrorTitle(error?.code) || error?.message || '数据问答加载失败'
  }
  finally {
    loading.value = false
  }
}

async function askQuestion() {
  if (containsRawSql(form.question_text)) {
    errorText.value = '问题内容不能包含原始 SQL'
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
          <span>数据问答</span>
          <el-button v-if="canAsk" type="primary" @click="askQuestion">
            提问
          </el-button>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form>
        <el-form-item label="问题">
          <el-input v-model="form.question_text" type="textarea" :rows="3" />
        </el-form-item>
        <el-form-item label="指标">
          <el-select v-model="form.metric_codes" multiple filterable style="width: 420px;">
            <el-option v-for="metric in metrics" :key="metric.metric_code" :label="metric.metric_name" :value="metric.metric_code" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-alert v-if="answerText" class="page-alert" type="success" show-icon :closable="true" :title="answerText" @close="answerText = ''" />
      <el-table v-loading="loading" :data="logs" row-key="id">
        <el-table-column prop="question_text" label="问题" min-width="240" />
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.status)">
              {{ aiStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="answer_text" label="回答" min-width="260" show-overflow-tooltip />
        <template #empty>
          <el-empty description="暂无数据问答记录" />
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

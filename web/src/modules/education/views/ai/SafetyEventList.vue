<script setup lang="ts">
import type { SafetyEventRecord } from '../../api/ai/safety.ts'
import { markSafetyHandled, pageSafetyEvents } from '../../api/ai/safety.ts'
import { aiErrorTitle, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiSafetyEventList' })

const loading = ref(false)
const rows = ref<SafetyEventRecord[]>([])
const total = ref(0)
const errorText = ref('')
const search = reactive({ page: 1, pageSize: 20, risk_level: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSafetyEvents(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    errorText.value = aiErrorTitle(error?.code) || error?.message || 'Safety events loading failed'
  }
  finally {
    loading.value = false
  }
}

async function handle(row: SafetyEventRecord) {
  await markSafetyHandled(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Safety Events</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-form inline>
        <el-form-item label="Risk">
          <el-select v-model="search.risk_level" clearable style="width: 140px;">
            <el-option label="blocked" value="blocked" />
            <el-option label="high" value="high" />
            <el-option label="warning" value="warning" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            Search
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="generation_task_id" label="Task" width="100" />
        <el-table-column label="Risk" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.risk_level)">
              {{ row.risk_level }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="event_type" label="Type" width="160" />
        <el-table-column prop="summary" label="Summary" min-width="240" />
        <el-table-column label="Actions" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.handled" @click="handle(row)">
              Handle
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No safety events" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.education-ai-page {
  .page-header { font-weight: 600; }
  .page-alert { margin-bottom: 12px; }
  .page-pagination { justify-content: flex-end; margin-top: 16px; }
}
</style>

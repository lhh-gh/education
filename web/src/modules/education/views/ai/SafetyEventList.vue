<script setup lang="ts">
import type { SafetyEventRecord } from '../../api/ai/safety.ts'
import { markSafetyHandled, pageSafetyEvents } from '../../api/ai/safety.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { aiErrorMessage, aiStatusLabel, aiTagType } from './aiRules.ts'

defineOptions({ name: 'EducationAiSafetyEventList' })

const message = useMessage()
const loading = ref(false)
const rows = ref<SafetyEventRecord[]>([])
const total = ref(0)
const errorText = ref('')
const successText = ref('')
const search = reactive({ page: 1, pageSize: 20, risk_level: '' })
const canHandle = computed(() => hasAuth('education:ai:safety:handle'))

function handleError(error: any, fallback: string) {
  errorText.value = aiErrorMessage(error, fallback)
  message.error(errorText.value)
}

async function loadRows() {
  loading.value = true
  try {
    const response = await pageSafetyEvents(search)
    rows.value = response.data.list
    total.value = response.data.total
    errorText.value = ''
  }
  catch (error: any) {
    handleError(error, '安全事件加载失败')
  }
  finally {
    loading.value = false
  }
}

async function handle(row: SafetyEventRecord) {
  try {
    await markSafetyHandled(row.id)
    successText.value = '安全事件已处理'
    message.success(successText.value)
    await loadRows()
  }
  catch (error: any) {
    handleError(error, '安全事件处理失败')
  }
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-ai-page pt-3">
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>安全事件</span>
        </div>
      </template>
      <el-alert v-if="errorText" class="page-alert" type="error" show-icon :closable="false" :title="errorText" />
      <el-alert v-if="successText" class="page-alert" type="success" show-icon :closable="true" :title="successText" @close="successText = ''" />
      <el-form inline>
        <el-form-item label="风险等级">
          <el-select v-model="search.risk_level" clearable style="width: 140px;">
            <el-option :label="aiStatusLabel('blocked')" value="blocked" />
            <el-option :label="aiStatusLabel('high')" value="high" />
            <el-option :label="aiStatusLabel('warning')" value="warning" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadRows">
            查询
          </el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="generation_task_id" label="任务ID" width="100" />
        <el-table-column label="风险等级" width="120">
          <template #default="{ row }">
            <el-tag :type="aiTagType(row.risk_level)">
              {{ aiStatusLabel(row.risk_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="event_type" label="事件类型" width="160" />
        <el-table-column prop="summary" label="摘要" min-width="240" />
        <el-table-column label="处理状态" width="120">
          <template #default="{ row }">
            <el-tag :type="row.handled ? 'success' : 'warning'">
              {{ row.handled ? '已处理' : '待处理' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button v-if="canHandle" link type="primary" :disabled="row.handled" @click="handle(row)">
              处理
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无安全事件" />
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

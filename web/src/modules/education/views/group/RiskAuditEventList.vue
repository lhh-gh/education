<script setup lang="ts">
import type { RiskAuditRecord } from '../../api/group/risk-audit.ts'
import { markRiskAuditHandled, pageRiskAuditEvents } from '../../api/group/risk-audit.ts'
import { groupRiskLabel, groupStatusLabel, groupTagType, riskAuditQueryParams } from './groupRules.ts'

defineOptions({ name: 'EducationGroupRiskAuditEventList' })

const loading = ref(false)
const rows = ref<RiskAuditRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, risk_level: '', handled: undefined as boolean | undefined })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageRiskAuditEvents(riskAuditQueryParams(search) as any)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function handle(row: RiskAuditRecord) {
  await markRiskAuditHandled(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="风险事件按风险等级、处理状态和数据范围过滤" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>风控审计</span>
          <el-button @click="loadRows">
            查询
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search">
        <el-form-item label="风险">
          <el-select v-model="search.risk_level" clearable>
            <el-option label="高风险" value="high" />
            <el-option label="严重" value="critical" />
            <el-option label="预警" value="warning" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="event_type" label="事件" width="180" />
        <el-table-column prop="summary" label="摘要" min-width="220" />
        <el-table-column label="风险" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.risk_level)">
              {{ groupRiskLabel(row.risk_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="处理状态" width="120">
          <template #default="{ row }">
            <el-tag :type="row.handled ? 'success' : 'warning'">
              {{ groupStatusLabel(row.handled ? 'handled' : 'open') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.handled" @click="handle(row)">
              标记处理
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无风险事件" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

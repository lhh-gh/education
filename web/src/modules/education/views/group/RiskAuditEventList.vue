<script setup lang="ts">
import type { RiskAuditRecord } from '../../api/group/risk-audit.ts'
import { markRiskAuditHandled, pageRiskAuditEvents } from '../../api/group/risk-audit.ts'
import { groupTagType, riskAuditQueryParams } from './groupRules.ts'

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
    <el-alert class="mb-3" type="info" title="Risk events are filtered by risk level, handled state, and data scope" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>Risk Audit Events</span>
          <el-button @click="loadRows">
            Search
          </el-button>
        </div>
      </template>
      <el-form :inline="true" :model="search">
        <el-form-item label="Risk">
          <el-select v-model="search.risk_level" clearable>
            <el-option label="High" value="high" />
            <el-option label="Critical" value="critical" />
            <el-option label="Warning" value="warning" />
          </el-select>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="event_type" label="Event" width="180" />
        <el-table-column prop="summary" label="Summary" min-width="220" />
        <el-table-column label="Risk" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.risk_level)">
              {{ row.risk_level }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Handled" width="120">
          <template #default="{ row }">
            <el-tag :type="row.handled ? 'success' : 'warning'">
              {{ row.handled ? 'handled' : 'open' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Action" width="120">
          <template #default="{ row }">
            <el-button link type="primary" :disabled="row.handled" @click="handle(row)">
              Mark
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="No risk events" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
  </div>
</template>

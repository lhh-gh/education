<script setup lang="ts">
import type { ContractRecord } from '../../api/group/contract.ts'
import { pageContracts, submitContractReview } from '../../api/group/contract.ts'
import ContractForm from './components/ContractForm.vue'
import { centsToYuan, groupRiskLabel, groupStatusLabel, groupTagType } from './groupRules.ts'

defineOptions({ name: 'EducationGroupContractList' })

const loading = ref(false)
const formVisible = ref(false)
const rows = ref<ContractRecord[]>([])
const total = ref(0)
const search = reactive({ page: 1, pageSize: 20, status: '' })

async function loadRows() {
  loading.value = true
  try {
    const response = await pageContracts(search)
    rows.value = response.data.list
    total.value = response.data.total
  }
  finally {
    loading.value = false
  }
}

async function submitReview(row: ContractRecord) {
  await submitContractReview(row.id)
  await loadRows()
}

onMounted(loadRows)
</script>

<template>
  <div class="mine-layout education-group-page pt-3">
    <el-alert class="mb-3" type="info" title="合同列表按当前集团数据范围过滤" show-icon />
    <el-card shadow="never">
      <template #header>
        <div class="page-header">
          <span>合同管理</span>
          <el-button type="primary" @click="formVisible = true">
            新增合同
          </el-button>
        </div>
      </template>
      <el-table v-loading="loading" :data="rows" row-key="id">
        <el-table-column prop="contract_no" label="合同编号" width="160" />
        <el-table-column prop="title" label="合同标题" min-width="180" />
        <el-table-column label="金额" width="140">
          <template #default="{ row }">
            {{ centsToYuan(row.amount_cents) }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.status)">
              {{ groupStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="风险" width="110">
          <template #default="{ row }">
            <el-tag :type="groupTagType(row.risk_level)">
              {{ groupRiskLabel(row.risk_level ?? 'normal') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="140">
          <template #default="{ row }">
            <el-button link type="primary" @click="submitReview(row)">
              提交审批
            </el-button>
          </template>
        </el-table-column>
        <template #empty>
          <el-empty description="暂无合同" />
        </template>
      </el-table>
      <el-pagination v-model:current-page="search.page" v-model:page-size="search.pageSize" class="page-pagination" layout="total, sizes, prev, pager, next" :total="total" @change="loadRows" />
    </el-card>
    <ContractForm v-model="formVisible" @success="loadRows" />
  </div>
</template>
